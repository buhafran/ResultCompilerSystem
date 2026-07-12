<?php

namespace App\Filament\School\Resources\SubjectResource\Pages;

use App\Filament\School\Resources\SubjectResource;
use App\Services\SubjectImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_template')
                ->label('CSV template')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn (): string => route('subjects.template', Filament::getTenant()))
                ->openUrlInNewTab(),

            Action::make('import')
                ->label('Bulk upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->schema([
                    FileUpload::make('file')
                        ->label('Subjects CSV')
                        ->disk('local')
                        ->directory('imports/subjects')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->maxSize(2048)
                        ->helperText('Columns: name, code, subtitle and is_active. Existing subject names are updated.')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = (string) $data['file'];

                    try {
                        $result = app(SubjectImportService::class)->import(Filament::getTenant(), Storage::disk('local')->path($path));
                        $message = "{$result['created']} created, {$result['updated']} updated, {$result['failed']} failed.";

                        if ($result['errors'] !== []) {
                            $message .= ' First errors: '.implode(' | ', array_slice($result['errors'], 0, 3));
                        }

                        Notification::make()
                            ->title('Subject import completed')
                            ->body($message)
                            ->color($result['failed'] > 0 ? 'warning' : 'success')
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($path);
                    }
                }),

            CreateAction::make(),
        ];
    }
}
