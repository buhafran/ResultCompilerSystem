<?php

namespace App\Filament\School\Resources\TeacherAssignmentResource\Pages;

use App\Enums\MembershipRole;
use App\Filament\School\Resources\TeacherAssignmentResource;
use App\Models\AcademicTerm;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CreateTeacherAssignment extends CreateRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $school = Filament::getTenant();

        throw_unless($school, RuntimeException::class, 'No active school tenant was found.');

        $teacherExists = $school->users()
            ->wherePivot('is_active', true)
            ->wherePivot('role', MembershipRole::Teacher->value)
            ->where('users.id', $data['user_id'])
            ->exists();

        abort_unless($teacherExists, 403, 'The selected teacher is not active in this school.');

        $termExists = AcademicTerm::query()
            ->where('school_id', $school->id)
            ->whereKey($data['academic_term_id'])
            ->exists();

        $classExists = SchoolClass::query()
            ->where('school_id', $school->id)
            ->whereKey($data['school_class_id'])
            ->exists();

        abort_unless($termExists && $classExists, 403, 'The selected term or class does not belong to this school.');

        $subjectIds = collect($data['subject_ids'] ?? [])
            ->filter()
            ->unique()
            ->values();

        $validSubjectIds = Subject::query()
            ->where('school_id', $school->id)
            ->whereIn('id', $subjectIds)
            ->pluck('id')
            ->map(fn ($id): string => (string) $id);

        abort_unless($subjectIds->map(fn ($id): string => (string) $id)->diff($validSubjectIds)->isEmpty(), 403, 'One or more subjects do not belong to this school.');

        $created = 0;
        $skipped = 0;

        foreach ($validSubjectIds as $subjectId) {
            $record = TeacherAssignment::firstOrCreate([
                'school_id' => $school->id,
                'user_id' => $data['user_id'],
                'academic_term_id' => $data['academic_term_id'],
                'school_class_id' => $data['school_class_id'],
                'subject_id' => $subjectId,
            ]);

            $record->wasRecentlyCreated ? $created++ : $skipped++;
        }

        Notification::make()
            ->success()
            ->title('Teacher assignments saved')
            ->body("{$created} subject assignment(s) added. {$skipped} duplicate(s) skipped.")
            ->send();

        return TeacherAssignment::query()
            ->where('school_id', $school->id)
            ->where('user_id', $data['user_id'])
            ->where('academic_term_id', $data['academic_term_id'])
            ->where('school_class_id', $data['school_class_id'])
            ->latest('id')
            ->firstOrFail();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
