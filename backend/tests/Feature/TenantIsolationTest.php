<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\ClassSubject;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_assignment_rejects_a_subject_from_another_school(): void
    {
        [$schoolA, $teacher, $term, $class] = $this->schoolContext('alpha');
        [$schoolB] = $this->schoolContext('beta');

        $foreignSubject = Subject::create([
            'school_id' => $schoolB->id,
            'name' => 'Foreign Mathematics',
            'code' => 'FMTH',
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);

        TeacherAssignment::create([
            'school_id' => $schoolA->id,
            'user_id' => $teacher->id,
            'academic_term_id' => $term->id,
            'school_class_id' => $class->id,
            'subject_id' => $foreignSubject->id,
        ]);
    }

    public function test_teacher_assignment_requires_a_class_subject_mapping(): void
    {
        [$school, $teacher, $term, $class] = $this->schoolContext('gamma');
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'English Language',
            'code' => 'ENG',
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);

        TeacherAssignment::create([
            'school_id' => $school->id,
            'user_id' => $teacher->id,
            'academic_term_id' => $term->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);
    }

    /** @return array{School,User,AcademicTerm,SchoolClass} */
    private function schoolContext(string $slug): array
    {
        $school = School::create(['name' => ucfirst($slug).' School', 'slug' => $slug, 'is_active' => true]);
        $teacher = User::create(['name' => ucfirst($slug).' Teacher', 'email' => $slug.'@example.test', 'password' => 'password']);
        $school->users()->attach($teacher->id, ['role' => MembershipRole::Teacher->value, 'is_active' => true]);
        $session = AcademicSession::create(['school_id' => $school->id, 'name' => '2026/2027', 'is_active' => true]);
        $term = AcademicTerm::create(['school_id' => $school->id, 'academic_session_id' => $session->id, 'name' => 'First Term', 'is_active' => true]);
        $class = SchoolClass::create(['school_id' => $school->id, 'name' => 'JSS 1 A', 'is_active' => true]);

        return [$school, $teacher, $term, $class];
    }
}
