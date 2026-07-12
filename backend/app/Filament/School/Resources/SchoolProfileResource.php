<?php

namespace App\Filament\School\Resources;

use App\Filament\School\Resources\SchoolProfileResource\Pages;
use App\Models\School;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SchoolProfileResource extends Resource
{
    protected static ?string $model = School::class;
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationLabel = 'School Profile';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';
    protected static string | \UnitEnum | null $navigationGroup = 'School Website';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereKey(Filament::getTenant()?->getKey());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSchoolManager(Filament::getTenant()) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isSchoolManager(Filament::getTenant()) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Public school page')->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('motto')->maxLength(255),
                Textarea::make('about')->rows(4)->maxLength(2000)->columnSpanFull(),
                TextInput::make('address')->maxLength(500)->columnSpanFull(),
                TextInput::make('phone')->tel()->maxLength(40),
                TextInput::make('email')->email()->maxLength(255),
                FileUpload::make('logo_path')->image()->disk('public')->imageEditor()->directory('schools/logos')->visibility('public')->maxSize(5120),
                TextInput::make('principal_name')->maxLength(255),
                FileUpload::make('principal_signature_path')->image()->disk('public')->directory('schools/signatures')->visibility('public')->maxSize(3072),
                DatePicker::make('next_term_begins_on'),
            ])->columns(2),

            Section::make('Result sheet options')
                ->description('Control what appears on report sheets generated for this school.')
                ->schema([
                    Toggle::make('settings.results.show_class_position')
                        ->label('Show student class position')
                        ->helperText('When disabled, class position is hidden from individual report sheets and combined report-card PDFs.')
                        ->default(true),
                ]),
            Section::make('Landing-page slider')
                ->description('Enable the slider after adding at least one active image under Landing Page Slides.')
                ->schema([
                    Toggle::make('settings.landing.slider_enabled')
                        ->label('Enable image slider')
                        ->default(false),
                    TextInput::make('settings.landing.slider_interval_seconds')
                        ->label('Seconds per slide')
                        ->integer()
                        ->minValue(3)
                        ->maxValue(15)
                        ->default(6),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('motto')->wrap(),
                TextColumn::make('slides_count')->counts('slides')->label('Slides'),
                IconColumn::make('settings.landing.slider_enabled')
                    ->label('Slider enabled')
                    ->boolean(),
                TextColumn::make('phone'),
                TextColumn::make('email'),
            ])
            ->recordActions([
                Action::make('viewLandingPage')
                    ->label('Open landing page')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (School $record): string => route('school.landing', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolProfiles::route('/'),
            'edit' => Pages\EditSchoolProfile::route('/{record}/edit'),
        ];
    }
}
