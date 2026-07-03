<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolAccessAndImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_only_active_accessible_schools(): void
    {
        $allowed = School::create(['name' => 'Allowed School', 'slug' => 'allowed', 'is_active' => true]);
        $disabledMembership = School::create(['name' => 'Disabled Membership', 'slug' => 'disabled-membership', 'is_active' => true]);
        $inactiveSchool = School::create(['name' => 'Inactive School', 'slug' => 'inactive-school', 'is_active' => false]);
        $user = User::create(['name' => 'Teacher', 'email' => 'teacher@example.test', 'password' => 'password123']);

        $allowed->users()->attach($user->id, ['role' => MembershipRole::Teacher->value, 'is_active' => true]);
        $disabledMembership->users()->attach($user->id, ['role' => MembershipRole::Teacher->value, 'is_active' => false]);
        $inactiveSchool->users()->attach($user->id, ['role' => MembershipRole::Teacher->value, 'is_active' => true]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'teacher@example.test',
            'password' => 'password123',
            'device_name' => 'Test device',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'user.schools')
            ->assertJsonPath('user.schools.0.slug', 'allowed');

        $this->assertTrue($user->canAccessTenant($allowed));
        $this->assertFalse($user->canAccessTenant($disabledMembership));
    }

    public function test_activating_a_term_deactivates_other_terms_and_sessions(): void
    {
        $school = School::create(['name' => 'Calendar School', 'slug' => 'calendar', 'is_active' => true]);
        $sessionOne = AcademicSession::create(['school_id' => $school->id, 'name' => '2025/2026', 'is_active' => true]);
        $termOne = AcademicTerm::create([
            'school_id' => $school->id,
            'academic_session_id' => $sessionOne->id,
            'name' => 'Third Term',
            'is_active' => true,
        ]);
        $sessionTwo = AcademicSession::create(['school_id' => $school->id, 'name' => '2026/2027', 'is_active' => false]);
        $termTwo = AcademicTerm::create([
            'school_id' => $school->id,
            'academic_session_id' => $sessionTwo->id,
            'name' => 'First Term',
            'is_active' => true,
        ]);

        $this->assertFalse($termOne->fresh()->is_active);
        $this->assertTrue($termTwo->fresh()->is_active);
        $this->assertFalse($sessionOne->fresh()->is_active);
        $this->assertTrue($sessionTwo->fresh()->is_active);
    }

    public function test_student_csv_import_creates_updates_and_rejects_unknown_classes(): void
    {
        Storage::fake('local');
        $school = School::create(['name' => 'Import School', 'slug' => 'import-school', 'is_active' => true]);
        $class = SchoolClass::create(['school_id' => $school->id, 'name' => 'JSS 1 A', 'is_active' => true]);
        $path = Storage::disk('local')->path('students.csv');
        file_put_contents($path, implode("\n", [
            'admission_number,first_name,middle_name,last_name,gender,date_of_birth,class_name,portal_pin,is_active',
            'ST-001,Amina,,Bello,female,2012-04-17,JSS 1 A,1234,yes',
            'ST-002,Bala,,Musa,male,2011-01-01,Unknown Class,1234,yes',
        ]));

        $result = app(StudentImportService::class)->import($school, $path);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(1, $result['failed']);
        $this->assertDatabaseHas('students', [
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => 'ST-001',
            'first_name' => 'Amina',
        ]);

        file_put_contents($path, implode("\n", [
            'admission_number,first_name,last_name,class_name,is_active',
            'ST-001,Aminah,Bello,JSS 1 A,no',
        ]));
        $updated = app(StudentImportService::class)->import($school, $path);

        $this->assertSame(0, $updated['created']);
        $this->assertSame(1, $updated['updated']);
        $this->assertSame(1, Student::where('school_id', $school->id)->count());
        $this->assertDatabaseHas('students', [
            'school_id' => $school->id,
            'admission_number' => 'ST-001',
            'first_name' => 'Aminah',
            'is_active' => false,
        ]);
    }
}
