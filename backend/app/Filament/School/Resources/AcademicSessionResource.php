<?php

namespace App\Filament\School\Resources;

use App\Filament\School\Resources\AcademicSessionResource\Pages;
use App\Models\AcademicSession;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademicSessionResource extends TenantManagedResource
{
    protected static ?string $model = AcademicSession::class;
    protected static ?string $navigationLabel = 'Sessions';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->placeholder('2026/2027')
                ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('school_id', Filament::getTenant()->id)),
            DatePicker::make('starts_on'),
            DatePicker::make('ends_on')->afterOrEqual('starts_on'),
            Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable(),
            TextColumn::make('starts_on')->date(),
            TextColumn::make('ends_on')->date(),
            TextColumn::make('terms_count')->counts('terms'),
            IconColumn::make('is_active')->boolean(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicSessions::route('/'),
            'create' => Pages\CreateAcademicSession::route('/create'),
            'edit' => Pages\EditAcademicSession::route('/{record}/edit'),
        ];
    }
}
