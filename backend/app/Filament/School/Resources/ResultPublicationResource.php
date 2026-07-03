<?php

namespace App\Filament\School\Resources;

use App\Enums\PublicationStatus;
use App\Filament\School\Resources\ResultPublicationResource\Pages;
use App\Models\ResultPublication;
use App\Services\ResultCompilerService;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResultPublicationResource extends TenantManagedResource
{
    protected static ?string $model = ResultPublication::class;
    protected static ?string $navigationLabel = 'Compile & Release';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rocket-launch';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('term.academicSession.name')->label('Session')->sortable(),
                TextColumn::make('term.name')->sortable(),
                TextColumn::make('schoolClass.name')->label('Class')->sortable(),
                TextColumn::make('version')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('statistics.student_count')->label('Students'),
                TextColumn::make('statistics.class_average')->label('Class Avg.')->suffix('%'),
                TextColumn::make('compiled_at')->dateTime(),
                TextColumn::make('released_at')->dateTime(),
            ])
            ->defaultSort('compiled_at', 'desc')
            ->recordActions([
                Action::make('release')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ResultPublication $record): bool => $record->status === PublicationStatus::Compiled)
                    ->action(fn (ResultPublication $record) => app(ResultCompilerService::class)->release(auth()->user(), $record))
                    ->successNotificationTitle('Results released to students'),
                Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ResultPublication $record): string => route('results.publication.preview', $record))
                    ->openUrlInNewTab(),
                Action::make('broadsheet')
                    ->label('Broadsheet PDF')
                    ->icon('heroicon-o-table-cells')
                    ->url(fn (ResultPublication $record): string => route('results.publication.broadsheet', $record))
                    ->openUrlInNewTab(),
                Action::make('report_cards')
                    ->label('All report cards')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (ResultPublication $record): string => route('results.publication.report-cards', $record))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListResultPublications::route('/')];
    }
}
