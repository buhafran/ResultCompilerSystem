<?php

namespace App\Services;

use App\Enums\MembershipRole;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\ClassSubject;
use App\Models\ResultEntry;
use App\Models\ResultPublication;
use App\Models\ResultSummary;
use App\Models\ResultTemplate;
use App\Models\SchoolClass;
use App\Models\SchoolMembership;
use App\Models\User;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SchoolOwnershipGuard
{
    public static function register(): void
    {
        AcademicSession::saved(function (AcademicSession $session): void {
            if ($session->is_active) {
                AcademicSession::query()
                    ->where('school_id', $session->school_id)
                    ->whereKeyNot($session->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'updated_at' => now()]);
            }
        });

        AcademicTerm::saving(function (AcademicTerm $term): void {
            self::sameSchool($term, AcademicSession::class, $term->academic_session_id, 'academic session');
        });

        AcademicTerm::saved(function (AcademicTerm $term): void {
            if ($term->is_active) {
                AcademicTerm::query()
                    ->where('school_id', $term->school_id)
                    ->whereKeyNot($term->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'updated_at' => now()]);

                AcademicSession::query()
                    ->where('school_id', $term->school_id)
                    ->whereKey($term->academic_session_id)
                    ->update(['is_active' => true, 'updated_at' => now()]);

                AcademicSession::query()
                    ->where('school_id', $term->school_id)
                    ->whereKeyNot($term->academic_session_id)
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'updated_at' => now()]);
            }
        });

        SchoolMembership::saving(function (SchoolMembership $membership): void {
            if (! $membership->school_id || ! $membership->user_id || ! User::query()->whereKey($membership->user_id)->exists()) {
                self::fail('The selected user is invalid.');
            }
        });

        SchoolClass::saving(function (SchoolClass $class): void {
            if ($class->class_teacher_id && ! DB::table('school_user')
                ->where('school_id', $class->school_id)
                ->where('user_id', $class->class_teacher_id)
                ->where('is_active', true)
                ->exists()) {
                self::fail('The selected class teacher is not an active member of this school.');
            }
        });

        Student::saving(function (Student $student): void {
            if ($student->school_class_id) {
                self::sameSchool($student, SchoolClass::class, $student->school_class_id, 'class');
            }
        });

        ClassSubject::saving(function (ClassSubject $mapping): void {
            self::sameSchool($mapping, SchoolClass::class, $mapping->school_class_id, 'class');
            self::sameSchool($mapping, Subject::class, $mapping->subject_id, 'subject');
        });

        TeacherAssignment::saving(function (TeacherAssignment $assignment): void {
            self::sameSchool($assignment, AcademicTerm::class, $assignment->academic_term_id, 'term');
            self::sameSchool($assignment, SchoolClass::class, $assignment->school_class_id, 'class');
            self::sameSchool($assignment, Subject::class, $assignment->subject_id, 'subject');

            $isTeacher = DB::table('school_user')
                ->where('school_id', $assignment->school_id)
                ->where('user_id', $assignment->user_id)
                ->where('role', MembershipRole::Teacher->value)
                ->where('is_active', true)
                ->exists();
            if (! $isTeacher) {
                self::fail('The selected user is not an active teacher in this school.');
            }

            $isClassSubject = ClassSubject::query()
                ->where('school_id', $assignment->school_id)
                ->where('school_class_id', $assignment->school_class_id)
                ->where('subject_id', $assignment->subject_id)
                ->exists();
            if (! $isClassSubject) {
                self::fail('Assign the subject to the class before assigning a teacher.');
            }
        });

        ResultEntry::saving(function (ResultEntry $entry): void {
            self::sameSchool($entry, AcademicTerm::class, $entry->academic_term_id, 'term');
            self::sameSchool($entry, SchoolClass::class, $entry->school_class_id, 'class');
            self::sameSchool($entry, Subject::class, $entry->subject_id, 'subject');
            self::sameSchool($entry, Student::class, $entry->student_id, 'student');

            $studentClass = Student::query()->whereKey($entry->student_id)->value('school_class_id');
            if ($studentClass && (int) $studentClass !== (int) $entry->school_class_id) {
                self::fail('The selected student does not belong to this class.');
            }
        });

        ResultPublication::saving(function (ResultPublication $publication): void {
            self::sameSchool($publication, AcademicTerm::class, $publication->academic_term_id, 'term');
            self::sameSchool($publication, SchoolClass::class, $publication->school_class_id, 'class');
            if ($publication->result_template_id) {
                self::sameSchool($publication, ResultTemplate::class, $publication->result_template_id, 'template');
            }
        });

        ResultSummary::saving(function (ResultSummary $summary): void {
            self::sameSchool($summary, AcademicTerm::class, $summary->academic_term_id, 'term');
            self::sameSchool($summary, SchoolClass::class, $summary->school_class_id, 'class');
            self::sameSchool($summary, Student::class, $summary->student_id, 'student');
            self::sameSchool($summary, ResultPublication::class, $summary->result_publication_id, 'publication');
        });

        ResultTemplate::saved(function (ResultTemplate $template): void {
            if ($template->is_default) {
                ResultTemplate::query()
                    ->where('school_id', $template->school_id)
                    ->whereKeyNot($template->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false, 'updated_at' => now()]);
            }
        });
    }

    /** @param class-string<Model> $related */
    private static function sameSchool(Model $owner, string $related, int|string|null $id, string $label): void
    {
        if (! $owner->school_id || ! $id || ! $related::query()->whereKey($id)->where('school_id', $owner->school_id)->exists()) {
            self::fail("The selected {$label} does not belong to this school.");
        }
    }

    private static function fail(string $message): never
    {
        throw ValidationException::withMessages(['school_id' => $message]);
    }
}
