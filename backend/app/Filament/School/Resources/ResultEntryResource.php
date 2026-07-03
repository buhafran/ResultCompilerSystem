<?php

namespace App\Filament\School\Resources;

use App\Enums\ResultEntryStatus;
use App\Filament\School\Resources\ResultEntryResource\Pages;
use App\Models\AcademicTerm;
use App\Models\ResultEntry;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResultEntryResource extends Resource
{
    protected static ?string $model = ResultEntry::class;
    protected static ?string $navigationLabel = 'Score Register';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    public static function getEloquentQuery(): Builder
    {
        $school = Filament::getTenant();
        $query = parent::getEloquentQuery()->where('school_id', $school->id);
        $user = auth()->user();

        if ($user && ! $user->isSchoolManager($school)) {
            $query->whereExists(function ($assignment) use ($user): void {
                $assignment->selectRaw('1')
                    ->from('teacher_assignments')
                    ->whereColumn('teacher_assignments.school_id', 'result_entries.school_id')
                    ->whereColumn('teacher_assignments.academic_term_id', 'result_entries.academic_term_id')
                    ->whereColumn('teacher_assignments.school_class_id', 'result_entries.school_class_id')
                    ->whereColumn('teacher_assignments.subject_id', 'result_entries.subject_id')
                    ->where('teacher_assignments.user_id', $user->id);
            });
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->canAccessTenant(Filament::getTenant());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('term.academicSession.name')->label('Session'),
            TextColumn::make('term.name'),
            TextColumn::make('schoolClass.name')->label('Class')->sortable(),
            TextColumn::make('subject.name')->sortable()->searchable(),
            TextColumn::make('student.full_name')->label('Student')->searchable(['first_name', 'last_name']),
            TextColumn::make('ca_score')->label('CA / 30'),
            TextColumn::make('exam_score')->label('Exam / 70'),
            TextColumn::make('total_score')->label('Total')->badge(),
            TextColumn::make('grade')->badge(),
            TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => $state instanceof ResultEntryStatus ? ucwords(str_replace('_', ' ', $state->value)) : $state),
            TextColumn::make('teacher.name'),
        ])->filters([
            SelectFilter::make('academic_term_id')->options(fn (): array => AcademicTerm::query()->with('academicSession')->where('school_id', Filament::getTenant()->id)->get()->mapWithKeys(fn (AcademicTerm $term): array => [$term->id => $term->academicSession->name.' - '.$term->name])->all()),
            SelectFilter::make('school_class_id')->options(fn (): array => Filament::getTenant()->classes()->orderBy('name')->pluck('name', 'id')->all()),
            SelectFilter::make('subject_id')->options(fn (): array => Filament::getTenant()->subjects()->orderBy('name')->pluck('name', 'id')->all()),
        ])->defaultSort('updated_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListResultEntries::route('/')];
    }
}
