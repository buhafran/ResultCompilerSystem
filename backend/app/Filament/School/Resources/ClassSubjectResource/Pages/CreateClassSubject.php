<?php

namespace App\Filament\School\Resources\ClassSubjectResource\Pages;

use App\Filament\School\Resources\ClassSubjectResource;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CreateClassSubject extends CreateRecord
{
    protected static string $resource = ClassSubjectResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $school = Filament::getTenant();

        throw_unless($school, RuntimeException::class, 'No active school tenant was found.');

        $classExists = SchoolClass::query()
            ->where('school_id', $school->id)
            ->whereKey($data['school_class_id'])
            ->exists();

        abort_unless($classExists, 403, 'The selected class does not belong to this school.');

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
