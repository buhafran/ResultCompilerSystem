<?php

namespace App\Filament\Platform\Resources;

use App\Enums\MembershipRole;
use App\Filament\Platform\Resources\UserResource\Pages;
use App\Models\School;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8),
                Toggle::make('is_super_admin')
                    ->label('Platform super administrator')
                    ->helperText('Platform super administrators can access every active school.'),
            ])->columns(2),
            Section::make('Accessible schools')
                ->description('These memberships are the authoritative school-access list used by the admin panel and teacher mobile API.')
                ->schema([
                    Repeater::make('memberships')
                        ->relationship()
                        ->schema([
                            Select::make('school_id')
                                ->label('School')
                                ->options(fn (): array => School::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                            Select::make('role')
                                ->options(collect(MembershipRole::cases())->mapWithKeys(
                                    fn (MembershipRole $role): array => [$role->value => $role->label()],
                                )->all())
                                ->required(),
                            Toggle::make('is_active')->default(true),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Grant access to a school')
                        ->reorderable(false)
                        ->collapsible()
                        ->itemLabel(function (array $state): ?string {
                            $school = ! empty($state['school_id']) ? School::find($state['school_id'])?->name : null;
                            $role = ! empty($state['role']) ? MembershipRole::tryFrom($state['role'])?->label() : null;

                            return trim(implode(' — ', array_filter([$school, $role]))) ?: 'School access';
                        })
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('activeSchools.name')->label('Accessible schools')->badge()->limitList(3)->expandableLimitedList(),
            IconColumn::make('is_super_admin')->boolean(),
            TextColumn::make('active_schools_count')->counts('activeSchools')->label('School count'),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
