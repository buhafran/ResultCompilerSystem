<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\PublicationStatus;
use App\Enums\ResultEntryStatus;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\ClassSubject;
use App\Models\ResultEntry;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\ResultCompilerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultCompilationTest extends TestCase
{
    use RefreshDatabase;

    public function test_compilation_builds_tie_safe_snapshots_and_release_withdraws_previous_version(): void
    {
        $school = School::create([
            'name' => 'Compilation School',
            'slug' => 'compilation-school',
            'is_active' => true,
            'settings' => ['assessment' => ['ca_max' => 30, 'exam_max' => 70, 'absent_counts_as_zero' => true]],
        ]);
        $admin = User::create(['name' => 'School Admin', 'email' => 'admin@example.test', 'password' => 'password']);
        $school->users()->attach($admin->id, ['role' => MembershipRole::SchoolAdmin->value, 'is_active' => true]);
        $session = AcademicSession::create(['school_id' => $school->id, 'name' => '2026/2027', 'is_active' => true]);
        $term = AcademicTerm::create(['school_id' => $school->id, 'academic_session_id' => $session->id, 'name' => 'First Term', 'is_active' => true]);
        $class = SchoolClass::create(['school_id' => $school->id, 'name' => 'JSS 1 A', 'is_active' => true]);
        $math = Subject::create(['school_id' => $school->id, 'name' => 'Mathematics', 'code' => 'MTH', 'is_active' => true]);
        $english = Subject::create(['school_id' => $school->id, 'name' => 'English', 'code' => 'ENG', 'is_active' => true]);
        foreach ([$math, $english] as $subject) {
            ClassSubject::create(['school_id' => $school->id, 'school_class_id' => $class->id, 'subject_id' => $subject->id]);
        }
        $students = collect([
            Student::create(['school_id' => $school->id, 'school_class_id' => $class->id, 'admission_number' => 'ST-001', 'first_name' => 'Amina', 'last_name' => 'One', 'is_active' => true]),
            Student::create(['school_id' => $school->id, 'school_class_id' => $class->id, 'admission_number' => 'ST-002', 'first_name' => 'Bala', 'last_name' => 'Two', 'is_active' => true]),
            Student::create(['school_id' => $school->id, 'school_class_id' => $class->id, 'admission_number' => 'ST-003', 'first_name' => 'Chidi', 'last_name' => 'Three', 'is_active' => true]),
        ]);

        $scores = [
            'ST-001' => [80, 80],
            'ST-002' => [70, 70],
            'ST-003' => [70, 70],
        ];
        foreach ($students as $student) {
            foreach ([$math, $english] as $index => $subject) {
                $total = $scores[$student->admission_number][$index];
                ResultEntry::create([
                    'school_id' => $school->id,
                    'academic_term_id' => $term->id,
                    'school_class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'student_id' => $student->id,
                    'ca_score' => 20,
                    'exam_score' => $total - 20,
                    'total_score' => $total,
                    'grade' => $total >= 70 ? 'A' : 'B',
                    'remark' => 'Ready',
                    'status' => ResultEntryStatus::Present,
                    'lock_version' => 1,
                ]);
            }
        }

        $service = app(ResultCompilerService::class);
        $versionOne = $service->compile($admin, $school, $term, $class);
        $positions = $versionOne->summaries()->orderByDesc('average_score')->pluck('class_position')->all();

        $this->assertTrue($term->fresh()->is_locked);
        $this->assertSame([1, 2, 2], $positions);
        $this->assertNotEmpty($versionOne->checksum);
        $this->assertCount(2, $versionOne->summaries()->first()->snapshot['subjects']);

        $service->release($admin, $versionOne);
        $this->assertSame(PublicationStatus::Released, $versionOne->fresh()->status);
        $versionOneToken = $versionOne->summaries()->firstOrFail()->public_token;
        $this->get(route('results.show', ['summary' => $versionOneToken]))->assertOk();

        $versionTwo = $service->compile($admin, $school, $term, $class);
        $service->release($admin, $versionTwo);

        $this->assertSame(PublicationStatus::Withdrawn, $versionOne->fresh()->status);
        $this->assertSame(PublicationStatus::Released, $versionTwo->fresh()->status);
        $this->get(route('results.show', ['summary' => $versionOneToken]))->assertNotFound();
    }
}
