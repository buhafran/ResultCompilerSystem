<?php

namespace App\Filament\School\Resources;

use App\Filament\School\Resources\ResultSummaryResource\Pages;
use App\Models\AcademicTerm;
use App\Models\ResultSummary;
use App\Services\AiCommentService;
use App\Services\AuditService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ResultSummaryResource extends TenantManagedResource
{
    protected static ?string $model = ResultSummary::class;

    protected static ?string $navigationLabel = 'Result Comments';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Reviewed comments')
                ->description('AI text is a draft. A school officer remains responsible for approving it.')
                ->schema([
                    Textarea::make('teacher_comment')
                        ->label('Teacher comment')
                        ->rows(4)
                        ->maxLength(500),

                    Textarea::make('principal_comment')
                        ->label('Principal comment')
                        ->rows(4)
                        ->maxLength(500),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('student.admission_number')
                    ->label('Admission number')
                    ->searchable(),

                TextColumn::make('term.academicSession.name')
                    ->label('Session'),

                TextColumn::make('term.name')
                    ->label('Term'),

                TextColumn::make('schoolClass.name')
                    ->label('Class'),

                TextColumn::make('average_score')
                    ->label('Average')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('class_position')
                    ->label('Position')
                    ->sortable(),

                IconColumn::make('ai_comment_generated')
                    ->label('AI draft')
                    ->boolean(),

                TextColumn::make('released_at')
                    ->label('Released')
                    ->dateTime()
                    ->placeholder('Not released'),
            ])
            ->filters([
                SelectFilter::make('academic_term_id')
                    ->label('Academic term')
                    ->options(fn (): array => AcademicTerm::query()
                        ->with('academicSession')
                        ->where('school_id', Filament::getTenant()->id)
                        ->get()
                        ->mapWithKeys(fn (AcademicTerm $term): array => [
                            $term->id => $term->academicSession->name.' - '.$term->name,
                        ])
                        ->all()),

                SelectFilter::make('school_class_id')
                    ->label('Class')
                    ->options(fn (): array => Filament::getTenant()
                        ->classes()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
            ])
            ->recordActions([
                Action::make('generateAi')
                    ->label('Generate AI draft')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Generate AI comment draft')
                    ->modalDescription('This will replace the current teacher and principal draft comments for this student.')
                    ->action(function (ResultSummary $record): void {
                        static::generateCommentForRecord($record);

                        Notification::make()
                            ->success()
                            ->title('Draft comments generated')
                            ->body('Review and edit the comments before release.')
                            ->send();
                    }),

                EditAction::make(),

                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ResultSummary $record): string => route('results.publication.preview', $record->result_publication_id))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkAction::make('generateAiComments')
                    ->label('Generate AI comments')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Generate comments for selected students')
                    ->modalDescription('AI drafts will be generated for all selected result summaries. Existing teacher and principal draft comments may be replaced. Use the table checkbox to select multiple students or all filtered students.')
                    ->modalSubmitActionLabel('Generate comments')
                    ->action(function (Collection $records): void {
                        $schoolId = Filament::getTenant()?->getKey();

                        if (! $schoolId) {
                            Notification::make()
                                ->danger()
                                ->title('School could not be identified')
                                ->body('Refresh the page and select the school again.')
                                ->send();

                            return;
                        }

                        $records = $records->filter(fn (ResultSummary $record): bool => (string) $record->school_id === (string) $schoolId);

                        if ($records->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title('No valid records selected')
                                ->body('Select one or more student result summaries for the current school.')
                                ->send();

                            return;
                        }

                        $generated = 0;
                        $failed = 0;
                        $failedStudents = [];

                        foreach ($records as $record) {
                            try {
                                static::generateCommentForRecord($record);
                                $generated++;
                            } catch (Throwable $exception) {
                                report($exception);
                                $failed++;
                                $failedStudents[] = $record->student?->full_name
                                    ?? $record->student?->admission_number
                                    ?? "Result #{$record->getKey()}";
                            }
                        }

                        if ($generated > 0 && $failed === 0) {
                            Notification::make()
                                ->success()
                                ->title('AI comments generated')
                                ->body("{$generated} student comment draft(s) were generated successfully.")
                                ->send();

                            return;
                        }

                        if ($generated > 0) {
                            $failedNames = collect($failedStudents)->take(5)->implode(', ');
                            $more = max(0, count($failedStudents) - 5);

                            Notification::make()
                                ->warning()
                                ->title('Comments partially generated')
                                ->body("{$generated} generated, {$failed} failed. Failed: {$failedNames}".($more > 0 ? " and {$more} more." : '.'))
                                ->persistent()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->danger()
                            ->title('Comment generation failed')
                            ->body('No comments were generated. Check the application log and AI service configuration.')
                            ->persistent()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    protected static function generateCommentForRecord(ResultSummary $record): void
    {
        $schoolId = Filament::getTenant()?->getKey();

        abort_unless(
            $schoolId && (string) $record->school_id === (string) $schoolId,
            403,
            'You cannot generate comments for another school.'
        );

        $record->loadMissing([
            'student',
            'schoolClass',
            'term.academicSession',
        ]);

        $comments = app(AiCommentService::class)->generate($record);

        $teacherComment = trim((string) ($comments['teacher_comment'] ?? ''));
        $principalComment = trim((string) ($comments['principal_comment'] ?? ''));

        if ($teacherComment === '' && $principalComment === '') {
            throw new RuntimeException('The AI service returned empty comments.');
        }

        DB::transaction(function () use ($record, $teacherComment, $principalComment): void {
            $lockedRecord = ResultSummary::query()
                ->whereKey($record->getKey())
                ->where('school_id', $record->school_id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = $lockedRecord->only([
                'teacher_comment',
                'principal_comment',
                'ai_comment_generated',
            ]);

            $lockedRecord->update([
                'teacher_comment' => mb_substr($teacherComment, 0, 500),
                'principal_comment' => mb_substr($principalComment, 0, 500),
                'ai_comment_generated' => true,
            ]);

            app(AuditService::class)->record(
                'result.ai_comment_generated',
                $lockedRecord,
                $before,
                $lockedRecord->fresh()->only([
                    'teacher_comment',
                    'principal_comment',
                    'ai_comment_generated',
                ]),
                $lockedRecord->school_id
            );
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResultSummaries::route('/'),
            'edit' => Pages\EditResultSummary::route('/{record}/edit'),
        ];
    }
}
