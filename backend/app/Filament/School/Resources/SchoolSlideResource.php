<?php

namespace App\Filament\School\Resources;

use App\Filament\School\Resources\SchoolSlideResource\Pages;
use App\Models\SchoolSlide;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchoolSlideResource extends TenantManagedResource
{
    protected static ?string $model = SchoolSlide::class;
    protected static ?string $navigationLabel = 'Landing Page Slides';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected static \UnitEnum|string|null $navigationGroup = 'School Website';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Slider content')
                ->description('Slides are displayed only when the landing-page slider is enabled in School Profile.')
                ->schema([
                    TextInput::make('title')->required()->maxLength(255),
                    Textarea::make('subtitle')->rows(3)->maxLength(600)->columnSpanFull(),
                    FileUpload::make('image_path')
                        ->label('Slide image')
                        ->image()
                        ->disk('public')
                        ->imageEditor()
                        ->directory('schools/slides')
                        ->visibility('public')
                        ->maxSize(10240)
                        ->required(),
                    TextInput::make('button_text')->maxLength(80)->placeholder('View results'),
                    TextInput::make('button_url')
                        ->url()
                        ->maxLength(2048)
                        ->placeholder('Leave blank to open the student result portal')
                        ->helperText('Use a complete https:// URL for external links.'),
                    TextInput::make('sort_order')->integer()->minValue(0)->default(0)->required(),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Image')->disk('public')->square(),
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolSlides::route('/'),
            'create' => Pages\CreateSchoolSlide::route('/create'),
            'edit' => Pages\EditSchoolSlide::route('/{record}/edit'),
        ];
    }
}
