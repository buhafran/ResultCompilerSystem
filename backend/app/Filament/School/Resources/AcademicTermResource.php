<?php

namespace App\Filament\School\Resources;

use App\Filament\School\Resources\AcademicTermResource\Pages;
use App\Models\AcademicTerm;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademicTermResource extends TenantManagedResource
{
    protected static ?string $model = AcademicTerm::class;
    protected static ?string $navigationLabel = 'Terms';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('academic_session_id')->options(fn (): array => Filament::getTenant()->academicSessions()->orderByDesc('starts_on')->pluck('name', 'id')->all())->required()->searchable()->preload(),
            TextInput::make('name')->required()->placeholder('First Term'),
            DatePicker::make('starts_on'),
            DatePicker::make('ends_on')->afterOrEqual('starts_on'),
            Toggle::make('is_active'),
            Toggle::make('is_locked')->helperText('Locked terms cannot accept score changes.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('academicSession.name')->label('Session')->sortable(),
            TextColumn::make('name')->sortable(),
            TextColumn::make('starts_on')->date(),
            TextColumn::make('ends_on')->date(),
            IconColumn::make('is_active')->boolean(),
            IconColumn::make('is_locked')->boolean(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicTerms::route('/'),
            'create' => Pages\CreateAcademicTerm::route('/create'),
            'edit' => Pages\EditAcademicTerm::route('/{record}/edit'),
        ];
    }
}
