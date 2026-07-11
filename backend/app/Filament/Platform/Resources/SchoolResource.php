<?php
namespace App\Filament\Platform\Resources;
use App\Filament\Platform\Resources\SchoolResource\Pages;
use App\Models\School;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class SchoolResource extends Resource
{
    protected static ?string $model = School::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'students as active_students_count' => fn (Builder $query): Builder => $query->where('is_active', true),
                'classes as active_classes_count' => fn (Builder $query): Builder => $query->where('is_active', true),
                'subjects as active_subjects_count' => fn (Builder $query): Builder => $query->where('is_active', true),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('School identity')->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('slug')->required()->unique(ignoreRecord: true)->alphaDash(),
                TextInput::make('custom_domain')->unique(ignoreRecord: true),
                TextInput::make('motto'),
                Textarea::make('about')->columnSpanFull(),
                TextInput::make('address')->columnSpanFull(),
                TextInput::make('phone'), TextInput::make('email')->email(),
                FileUpload::make('logo_path')->image()->disk('public')->directory('schools/logos')->visibility('public')->maxSize(5120),
                Toggle::make('is_active')->default(true),
            ])->columns(2),
            Section::make('Default settings')->schema([KeyValue::make('settings')->columnSpanFull()]),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('slug')->copyable(),
            TextColumn::make('users_count')->counts('users')->label('Users'),
            TextColumn::make('active_students_count')->label('Active students')->sortable(),
            TextColumn::make('active_classes_count')->label('Active classes')->sortable(),
            TextColumn::make('active_subjects_count')->label('Active subjects')->sortable(),
            IconColumn::make('is_active')->boolean(), TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
    public static function getPages(): array { return ['index'=>Pages\ListSchools::route('/'),'create'=>Pages\CreateSchool::route('/create'),'edit'=>Pages\EditSchool::route('/{record}/edit')]; }
}
