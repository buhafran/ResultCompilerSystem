<?php

namespace App\Filament\School\Resources\TeacherAssignmentResource\Pages;

use App\Filament\School\Resources\TeacherAssignmentResource;
use App\Models\TeacherAssignment;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTeacherAssignment extends CreateRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $school = Filament::getTenant();

        $created = 0;
        $skipped = 0;

        foreach ($data['subject_ids'] ?? [] as $subjectId) {
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