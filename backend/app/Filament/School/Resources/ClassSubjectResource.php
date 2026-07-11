<?php

namespace App\Filament\School\Resources;

use App\Filament\School\Resources\ClassSubjectResource\Pages;
use App\Models\ClassSubject;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassSubjectResource extends TenantManagedResource
{
    protected static ?string $model = ClassSubject::class;
    protected static ?string $navigationLabel = 'Class Subjects';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('school_class_id')->options(fn (): array => Filament::getTenant()->classes()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload()->required(),
            Select::make('subject_id')->options(fn (): array => Filament::getTenant()->subjects()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload()->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('schoolClass.name')->label('Class')->sortable(),
            TextColumn::make('subject.name')->sortable()->searchable(),
        ])->recordActions([DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassSubjects::route('/'),
            'create' => Pages\CreateClassSubject::route('/create'),
            'edit' => Pages\EditClassSubject::route('/{record}/edit'),
        ];
    }
}
