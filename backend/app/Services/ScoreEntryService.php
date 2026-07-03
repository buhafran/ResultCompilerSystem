<?php

namespace App\Services;

use App\Enums\ResultEntryStatus;
use App\Models\AcademicTerm;
use App\Models\ResultEntry;
use App\Models\School;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Support\GradeScale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ScoreEntryService
{
    public function __construct(private readonly AuditService $audit) {}

    /** @param array{student_id:int,ca_score?:float|int|null,exam_score?:float|int|null,status?:string,lock_version?:int} $payload */
    public function save(User $actor, School $school, TeacherAssignment $assignment, array $payload): ResultEntry
    {
        $this->authorizeAssignment($actor, $school, $assignment);
        $term = AcademicTerm::query()->where('school_id', $school->id)->findOrFail($assignment->academic_term_id);

        if ($term->is_locked) {
            throw ValidationException::withMessages(['term' => 'This term is locked. Results can no longer be edited.']);
        }

        $student = Student::query()
            ->where('school_id', $school->id)
            ->where('school_class_id', $assignment->school_class_id)
            ->where('is_active', true)
            ->findOrFail($payload['student_id']);

        $status = ResultEntryStatus::tryFrom($payload['status'] ?? ResultEntryStatus::Present->value)
            ?? ResultEntryStatus::Present;
        $caMax = (float) $school->setting('assessment.ca_max', config('result-system.ca_max', 30));
        $examMax = (float) $school->setting('assessment.exam_max', config('result-system.exam_max', 70));
        $ca = $status === ResultEntryStatus::Present ? $this->score($payload['ca_score'] ?? null, $caMax, 'CA') : null;
        $exam = $status === ResultEntryStatus::Present ? $this->score($payload['exam_score'] ?? null, $examMax, 'Examination') : null;
        $total = $status === ResultEntryStatus::Present ? round($ca + $exam, 2) : null;
        $grade = $status === ResultEntryStatus::Absent
            ? ['grade' => 'ABS', 'remark' => 'Absent']
            : GradeScale::from($school->setting('grading.scale'))->evaluate((float) $total);

        return DB::transaction(function () use ($actor, $school, $assignment, $student, $payload, $status, $ca, $exam, $total, $grade): ResultEntry {
            $entry = ResultEntry::query()->lockForUpdate()->firstOrNew([
                'school_id' => $school->id,
                'academic_term_id' => $assignment->academic_term_id,
                'student_id' => $student->id,
                'subject_id' => $assignment->subject_id,
            ]);

            if ($entry->exists && isset($payload['lock_version']) && (int) $payload['lock_version'] !== $entry->lock_version) {
                throw ValidationException::withMessages(['lock_version' => 'This score was changed on another device. Refresh before saving.']);
            }

            $before = $entry->exists ? $entry->toArray() : null;
            $entry->fill([
                'school_class_id' => $assignment->school_class_id,
                'teacher_id' => $actor->id,
                'ca_score' => $ca,
                'exam_score' => $exam,
                'total_score' => $total,
                'grade' => $grade['grade'],
                'remark' => $grade['remark'],
                'status' => $status,
                'submitted_at' => now(),
                'lock_version' => ($entry->lock_version ?: 0) + 1,
            ])->save();

            $this->audit->record('result.score_saved', $entry, $before, $entry->fresh()->toArray(), $school->id);
            return $entry->fresh(['student', 'subject']);
        });
    }

    private function authorizeAssignment(User $actor, School $school, TeacherAssignment $assignment): void
    {
        $valid = $actor->canAccessTenant($school)
            && $assignment->school_id === $school->id
            && ($actor->isSchoolManager($school) || $assignment->user_id === $actor->id);

        if (! $valid) {
            throw new AuthorizationException('You are not assigned to this class and subject.');
        }
    }

    private function score(float|int|string|null $value, float $max, string $label): float
    {
        if ($value === null || $value === '') {
            throw ValidationException::withMessages(['score' => "{$label} score is required for a present student."]);
        }
        $value = (float) $value;
        if ($value < 0 || $value > $max) {
            throw ValidationException::withMessages(['score' => "{$label} score must be between 0 and {$max}."]);
        }
        return round($value, 2);
    }
}
