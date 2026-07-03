<?php

namespace App\Services;

use App\Enums\PublicationStatus;
use App\Enums\ResultEntryStatus;
use App\Models\AcademicTerm;
use App\Models\ResultEntry;
use App\Models\ResultPublication;
use App\Models\ResultSummary;
use App\Models\ResultTemplate;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Support\CompetitionRanker;
use App\Support\GradeScale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ResultCompilerService
{
    public function __construct(private readonly AuditService $audit) {}

    public function compile(User $actor, School $school, AcademicTerm $term, SchoolClass $class, ?ResultTemplate $template = null): ResultPublication
    {
        if (! $actor->isSchoolManager($school)) {
            throw new AuthorizationException('Only a school administrator or examination officer can compile results.');
        }
        $this->assertOwned($school, $term, $class, $template);

        return DB::transaction(function () use ($actor, $school, $term, $class, $template): ResultPublication {
            $term = AcademicTerm::query()
                ->where('school_id', $school->id)
                ->lockForUpdate()
                ->findOrFail($term->id);
            if (! $term->is_locked) {
                $term->update(['is_locked' => true]);
            }
            $term->loadMissing('academicSession');

            $class = SchoolClass::query()->where('school_id', $school->id)->lockForUpdate()->findOrFail($class->id);
            $students = Student::query()->where('school_id', $school->id)->where('school_class_id', $class->id)->where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get();
            $subjectIds = DB::table('class_subjects')->where('school_id', $school->id)->where('school_class_id', $class->id)->pluck('subject_id');

            if ($students->isEmpty() || $subjectIds->isEmpty()) {
                throw ValidationException::withMessages(['class' => 'The class must contain active students and assigned subjects.']);
            }

            $this->createMissingSheets($school, $term, $class, $students, $subjectIds);
            $entries = ResultEntry::query()
                ->with(['student:id,first_name,middle_name,last_name,admission_number,gender', 'subject:id,name,code'])
                ->where('school_id', $school->id)
                ->where('academic_term_id', $term->id)
                ->where('school_class_id', $class->id)
                ->whereIn('subject_id', $subjectIds)
                ->lockForUpdate()
                ->get();

            $expected = $students->count() * $subjectIds->count();
            $incomplete = $entries->where('status', ResultEntryStatus::NotEntered)->count();
            if ($entries->count() !== $expected || $incomplete > 0) {
                throw ValidationException::withMessages([
                    'results' => "Compilation stopped: {$incomplete} score sheets are not completed. Mark absent students explicitly.",
                ]);
            }

            $this->recalculateAndRankSubjects($school, $entries);
            $entries = $entries->fresh(['student', 'subject']);
            $nextVersion = (int) ResultPublication::query()
                ->where('school_id', $school->id)
                ->where('academic_term_id', $term->id)
                ->where('school_class_id', $class->id)
                ->max('version') + 1;

            $publication = ResultPublication::create([
                'school_id' => $school->id,
                'academic_term_id' => $term->id,
                'school_class_id' => $class->id,
                'result_template_id' => $template?->id,
                'version' => $nextVersion,
                'status' => PublicationStatus::Compiled,
                'compiled_by' => $actor->id,
                'compiled_at' => now(),
            ]);

            $summaryPayloads = $this->buildSummaryPayloads($school, $term, $class, $publication, $students, $entries, $subjectIds->count());
            $averages = collect($summaryPayloads)->pluck('average_score');
            $highestAverage = round((float) $averages->max(), 2);
            $lowestAverage = round((float) $averages->min(), 2);
            $classRanks = CompetitionRanker::rank(collect($summaryPayloads)->mapWithKeys(fn (array $item) => [$item['student_id'] => $item['average_score']])->all());
            foreach ($summaryPayloads as $payload) {
                $payload['class_position'] = $classRanks[$payload['student_id']] ?? null;
                $payload['snapshot']['summary']['class_position'] = $payload['class_position'];
                $payload['snapshot']['summary']['highest_average'] = $highestAverage;
                $payload['snapshot']['summary']['lowest_average'] = $lowestAverage;
                ResultSummary::create($payload);
            }

            $stats = [
                'student_count' => $students->count(),
                'subject_count' => $subjectIds->count(),
                'class_average' => round((float) $averages->avg(), 2),
                'highest_average' => $highestAverage,
                'lowest_average' => $lowestAverage,
            ];
            $checksum = hash('sha256', json_encode(ResultSummary::where('result_publication_id', $publication->id)->orderBy('student_id')->pluck('snapshot')->all(), JSON_THROW_ON_ERROR));
            $publication->update(['statistics' => $stats, 'checksum' => $checksum]);
            $this->audit->record('result.compiled', $publication, null, $publication->fresh()->toArray(), $school->id);

            return $publication->fresh(['summaries', 'term.academicSession', 'schoolClass', 'template']);
        }, 3);
    }

    public function release(User $actor, ResultPublication $publication): ResultPublication
    {
        $publication->loadMissing('school');
        if (! $actor->isSchoolManager($publication->school)) {
            throw new AuthorizationException('Only a school administrator or examination officer can release results.');
        }
        if ($publication->status !== PublicationStatus::Compiled) {
            throw ValidationException::withMessages(['publication' => 'Only a compiled result can be released.']);
        }

        return DB::transaction(function () use ($actor, $publication): ResultPublication {
            $publication = ResultPublication::query()->whereKey($publication->id)->lockForUpdate()->firstOrFail();
            if ($publication->status !== PublicationStatus::Compiled) {
                throw ValidationException::withMessages(['publication' => 'This result version is no longer available for release.']);
            }
            $before = $publication->toArray();
            $releasedAt = now();

            ResultPublication::query()
                ->where('school_id', $publication->school_id)
                ->where('academic_term_id', $publication->academic_term_id)
                ->where('school_class_id', $publication->school_class_id)
                ->whereKeyNot($publication->id)
                ->where('status', PublicationStatus::Released->value)
                ->update(['status' => PublicationStatus::Withdrawn->value, 'updated_at' => $releasedAt]);

            $publication->update([
                'status' => PublicationStatus::Released,
                'released_by' => $actor->id,
                'released_at' => $releasedAt,
            ]);
            $publication->summaries()->update(['released_at' => $releasedAt]);
            $this->audit->record('result.released', $publication, $before, $publication->fresh()->toArray(), $publication->school_id);

            return $publication->fresh(['summaries']);
        });
    }

    private function assertOwned(School $school, AcademicTerm $term, SchoolClass $class, ?ResultTemplate $template): void
    {
        if ($term->school_id !== $school->id || $class->school_id !== $school->id || ($template && $template->school_id !== $school->id)) {
            throw new AuthorizationException('Cross-school access was blocked.');
        }
    }

    private function createMissingSheets(School $school, AcademicTerm $term, SchoolClass $class, Collection $students, Collection $subjectIds): void
    {
        $now = now();
        $rows = [];
        foreach ($students as $student) {
            foreach ($subjectIds as $subjectId) {
                $rows[] = [
                    'school_id' => $school->id,
                    'academic_term_id' => $term->id,
                    'school_class_id' => $class->id,
                    'subject_id' => $subjectId,
                    'student_id' => $student->id,
                    'status' => ResultEntryStatus::NotEntered->value,
                    'lock_version' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            ResultEntry::query()->insertOrIgnore($chunk);
        }
    }

    private function recalculateAndRankSubjects(School $school, Collection $entries): void
    {
        $scale = GradeScale::from($school->setting('grading.scale'));
        foreach ($entries as $entry) {
            if ($entry->status === ResultEntryStatus::Present) {
                $total = round((float) $entry->ca_score + (float) $entry->exam_score, 2);
                $grade = $scale->evaluate($total);
                $entry->update(['total_score' => $total, 'grade' => $grade['grade'], 'remark' => $grade['remark']]);
            } else {
                $entry->update(['total_score' => null, 'grade' => 'ABS', 'remark' => 'Absent', 'subject_position' => null]);
            }
        }

        foreach ($entries->groupBy('subject_id') as $subjectEntries) {
            $rankable = $subjectEntries->filter(fn (ResultEntry $entry) => $entry->status === ResultEntryStatus::Present);
            $ranks = CompetitionRanker::rank($rankable->mapWithKeys(fn (ResultEntry $entry) => [$entry->id => (float) $entry->total_score])->all());
            foreach ($subjectEntries as $entry) {
                $entry->update(['subject_position' => $ranks[$entry->id] ?? null]);
            }
        }
    }

    /** @return array<int, array<string,mixed>> */
    private function buildSummaryPayloads(School $school, AcademicTerm $term, SchoolClass $class, ResultPublication $publication, Collection $students, Collection $entries, int $subjectCount): array
    {
        $absentCountsAsZero = (bool) $school->setting('assessment.absent_counts_as_zero', true);
        $payloads = [];
        foreach ($students as $student) {
            $studentEntries = $entries->where('student_id', $student->id)->sortBy(fn (ResultEntry $e) => $e->subject->name);
            $present = $studentEntries->where('status', ResultEntryStatus::Present);
            $total = round((float) $present->sum('total_score'), 2);
            $denominator = $absentCountsAsZero ? $subjectCount : max(1, $present->count());
            $average = round($total / max(1, $denominator), 2);
            $subjectRows = $studentEntries->map(fn (ResultEntry $entry) => [
                'subject' => $entry->subject->name,
                'subject_code' => $entry->subject->code,
                'ca_score' => $entry->ca_score !== null ? (float) $entry->ca_score : null,
                'exam_score' => $entry->exam_score !== null ? (float) $entry->exam_score : null,
                'total_score' => $entry->total_score !== null ? (float) $entry->total_score : null,
                'grade' => $entry->grade,
                'remark' => $entry->remark,
                'position' => $entry->subject_position,
                'status' => $entry->status->value,
            ])->values()->all();

            $payloads[] = [
                'result_publication_id' => $publication->id,
                'school_id' => $school->id,
                'academic_term_id' => $term->id,
                'school_class_id' => $class->id,
                'student_id' => $student->id,
                'total_score' => $total,
                'average_score' => $average,
                'subject_count' => $subjectCount,
                'public_token' => (string) Str::uuid(),
                'snapshot' => [
                    'schema_version' => 1,
                    'school' => ['name' => $school->name, 'motto' => $school->motto, 'address' => $school->address, 'logo_path' => $school->logo_path, 'principal_name' => $school->principal_name, 'principal_signature_path' => $school->principal_signature_path],
                    'student' => ['admission_number' => $student->admission_number, 'name' => $student->full_name, 'gender' => $student->gender],
                    'academic' => ['session' => $term->academicSession->name, 'term' => $term->name, 'class' => $class->name],
                    'subjects' => $subjectRows,
                    'summary' => ['total_score' => $total, 'average_score' => $average, 'subject_count' => $subjectCount, 'class_position' => null, 'class_size' => $students->count()],
                    'grading_scale' => GradeScale::from($school->setting('grading.scale'))->bands(),
                    'next_term_begins_on' => optional($school->next_term_begins_on)->format('Y-m-d'),
                ],
            ];
        }
        return $payloads;
    }
}
