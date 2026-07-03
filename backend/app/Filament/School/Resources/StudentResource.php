<?php

namespace App\Filament\School\Resources;

use App\Filament\School\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class StudentResource extends TenantManagedResource
{
    protected static ?string $model = Student::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Student profile')->schema([
                TextInput::make('admission_number')
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('school_id', Filament::getTenant()->id)),
                Select::make('school_class_id')
                    ->options(fn (): array => Filament::getTenant()->classes()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->preload()->required(),
                TextInput::make('first_name')->required(),
                TextInput::make('middle_name'),
                TextInput::make('last_name')->required(),
                Select::make('gender')->options(['male' => 'Male', 'female' => 'Female']),
                DatePicker::make('date_of_birth'),
                FileUpload::make('photo_path')->image()->disk('public')->directory('students/photos')->visibility('public')->maxSize(5120),
                Toggle::make('is_active')->default(true),
                TextInput::make('portal_pin_hash')
                    ->label('New portal PIN')->password()->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->helperText('Leave blank to keep the current PIN.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('admission_number')->searchable()->copyable(),
            TextColumn::make('full_name')->label('Student')->searchable(['first_name', 'middle_name', 'last_name'])->sortable(['last_name']),
            TextColumn::make('schoolClass.name')->label('Class')->sortable(),
            TextColumn::make('gender'),
            IconColumn::make('is_active')->boolean(),
        ])->defaultSort('last_name')->recordActions([EditAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
