<?php

namespace App\Filament\School\Resources\ClassSubjectResource\Pages;

use App\Filament\School\Resources\ClassSubjectResource;
use App\Models\ClassSubject;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateClassSubject extends CreateRecord
{
    protected static string $resource = ClassSubjectResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $school = Filament::getTenant();

        $created = 0;
        $skipped = 0;

        foreach ($data['subject_ids'] ?? [] as $subjectId) {
            $record = ClassSubject::firstOrCreate([
                'school_id' => $school->id,
                'school_class_id' => $data['school_class_id'],
                'subject_id' => $subjectId,
            ]);

            $record->wasRecentlyCreated ? $created++ : $skipped++;
        }

        Notification::make()
            ->success()
            ->title('Class subjects saved')
            ->body("{$created} subject(s) added. {$skipped} duplicate(s) skipped.")
            ->send();

        return ClassSubject::query()
            ->where('school_id', $school->id)
            ->where('school_class_id', $data['school_class_id'])
            ->latest('id')
            ->firstOrFail();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}