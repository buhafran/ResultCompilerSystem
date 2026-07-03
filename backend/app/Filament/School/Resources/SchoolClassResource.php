<?php

namespace App\Filament\School\Resources;

use App\Enums\MembershipRole;
use App\Filament\School\Resources\SchoolClassResource\Pages;
use App\Models\SchoolClass;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchoolClassResource extends TenantManagedResource
{
    protected static ?string $model = SchoolClass::class;
    protected static ?string $navigationLabel = 'Classes';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('school_id', Filament::getTenant()->id)),
            TextInput::make('level'),
            TextInput::make('arm'),
            Select::make('class_teacher_id')->label('Class teacher')->options(fn (): array => Filament::getTenant()->users()
                ->wherePivot('is_active', true)->wherePivot('role', MembershipRole::Teacher->value)
                ->orderBy('users.name')->pluck('users.name', 'users.id')->all())->searchable()->preload(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('level'),
            TextColumn::make('classTeacher.name')->label('Class teacher'),
            TextColumn::make('students_count')->counts('students'),
            IconColumn::make('is_active')->boolean(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'edit' => Pages\EditSchoolClass::route('/{record}/edit'),
        ];
    }
}
