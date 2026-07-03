<?php

namespace App\Filament\School\Resources;

use App\Enums\MembershipRole;
use App\Filament\School\Resources\TeacherAssignmentResource\Pages;
use App\Models\AcademicTerm;
use App\Models\TeacherAssignment;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeacherAssignmentResource extends TenantManagedResource
{
    protected static ?string $model = TeacherAssignment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Teacher')
                ->options(fn (): array => Filament::getTenant()->users()
                    ->wherePivot('is_active', true)
                    ->wherePivot('role', MembershipRole::Teacher->value)
                    ->orderBy('users.name')
                    ->pluck('users.name', 'users.id')
                    ->all())
                ->searchable()
                ->preload()
                ->required(),
    
            Select::make('academic_term_id')
                ->label('Term')
                ->options(fn (): array => AcademicTerm::query()
                    ->with('academicSession')
                    ->where('school_id', Filament::getTenant()->id)
                    ->orderByDesc('starts_on')
                    ->get()
                    ->mapWithKeys(fn (AcademicTerm $term): array => [
                        $term->id => $term->academicSession->name . ' - ' . $term->name,
                    ])
                    ->all())
                ->searchable()
                ->preload()
                ->required(),
    
            Select::make('school_class_id')
                ->label('Class')
                ->options(fn (): array => Filament::getTenant()
                    ->classes()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->searchable()
                ->preload()
                ->required(),
    
            Select::make('subject_ids')
                ->label('Subjects')
                ->multiple()
                ->options(fn (): array => Filament::getTenant()
                    ->subjects()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->searchable()
                ->preload()
                ->required()
                ->hiddenOn('edit'),
    
            Select::make('subject_id')
                ->label('Subject')
                ->options(fn (): array => Filament::getTenant()
                    ->subjects()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->searchable()
                ->preload()
                ->required()
                ->hiddenOn('create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('teacher.name')->searchable(),
            TextColumn::make('term.academicSession.name')->label('Session'),
            TextColumn::make('term.name'),
            TextColumn::make('schoolClass.name')->label('Class')->sortable(),
            TextColumn::make('subject.name')->sortable(),
        ])->recordActions([DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherAssignments::route('/'),
            'create' => Pages\CreateTeacherAssignment::route('/create'),
            'edit' => Pages\EditTeacherAssignment::route('/{record}/edit'),
        ];
    }
}
