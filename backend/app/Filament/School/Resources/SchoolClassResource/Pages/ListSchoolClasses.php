<?php

namespace App\Filament\School\Resources\SchoolClassResource\Pages;

use App\Filament\School\Resources\SchoolClassResource;
use App\Services\ClassImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListSchoolClasses extends ListRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_template')
                ->label('CSV template')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn (): string => route('classes.template', Filament::getTenant()))
                ->openUrlInNewTab(),

            Action::make('import')
                ->label('Bulk upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->schema([
                    FileUpload::make('file')
                        ->label('Classes CSV')
                        ->disk('local')
                        ->directory('imports/classes')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->maxSize(2048)
                        ->helperText('Columns: name, level, arm and is_active. Existing class names are updated.')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = (string) $data['file'];

                    try {
                        $result = app(ClassImportService::class)->import(Filament::getTenant(), Storage::disk('local')->path($path));
                        $message = "{$result['created']} created, {$result['updated']} updated, {$result['failed']} failed.";

                        if ($result['errors'] !== []) {
                            $message .= ' First errors: '.implode(' | ', array_slice($result['errors'], 0, 3));
                        }

                        Notification::make()
                            ->title('Class import completed')
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
