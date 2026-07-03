<?php

namespace App\Filament\School\Resources\StudentResource\Pages;

use App\Filament\School\Resources\StudentResource;
use App\Services\StudentImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_template')
                ->label('CSV template')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn (): string => route('students.template', Filament::getTenant()))
                ->openUrlInNewTab(),
            Action::make('import')
                ->label('Bulk upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->schema([
                    FileUpload::make('file')
                        ->label('Students CSV')
                        ->disk('local')
                        ->directory('imports/students')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->maxSize(5120)
                        ->helperText('Required columns: admission_number, first_name, last_name and class_name. Existing admission numbers are updated.')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = (string) $data['file'];
                    try {
                        $result = app(StudentImportService::class)->import(Filament::getTenant(), Storage::disk('local')->path($path));
                        $message = "{$result['created']} created, {$result['updated']} updated, {$result['failed']} failed.";
                        if ($result['errors'] !== []) {
                            $message .= ' First errors: '.implode(' | ', array_slice($result['errors'], 0, 3));
                        }

                        Notification::make()
                            ->title('Student import completed')
                            ->body($message)
                            ->color($result['failed'] > 0 ? 'warning' : 'success')
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($path);
                    }
                }),
            Action::make('export')
                ->label('Download students')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => route('students.export', Filament::getTenant()))
                ->openUrlInNewTab(),
            CreateAction::make(),
        ];
    }
}
