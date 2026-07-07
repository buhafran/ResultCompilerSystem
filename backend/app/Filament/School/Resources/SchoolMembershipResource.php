<?php

namespace App\Filament\School\Resources;

use App\Enums\MembershipRole;
use App\Filament\School\Resources\SchoolMembershipResource\Pages;
use App\Models\SchoolMembership;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class SchoolMembershipResource extends TenantManagedResource
{
    protected static ?string $model = SchoolMembership::class;
    protected static ?string $navigationLabel = 'Users & Roles';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('User')
                ->helperText('School administrators may select an unassigned platform user or create a new account. Cross-school access is granted only from the platform panel.')
                ->options(function (?SchoolMembership $record): array {
                    $schoolId = Filament::getTenant()?->getKey();
                    if (! $schoolId) {
                        return [];
                    }

                    return User::query()
                        ->where(function (Builder $query) use ($schoolId, $record): void {
                            $query->whereDoesntHave('memberships')
                            ->where('last_school_id','=',schoolId);

                            // if ($record?->user_id) {
                        
                            //     $query->orWhereKey($record->user_id);
                            // }
                        })
                        ->orderBy('name')
                        ->limit(250)
                        ->get(['id', 'name', 'email'])
                        ->mapWithKeys(fn (User $user): array => [$user->id => "{$user->name} ({$user->email})"])
                        ->all();
                })
                ->getSearchResultsUsing(function (string $search, ?SchoolMembership $record): array {
                    $schoolId = Filament::getTenant()?->getKey();
                    if (! $schoolId) {
                        return [];
                    }

                    return User::query()
                        ->where(function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->where(function (Builder $query) use ($schoolId, $record): void {
                            $query->whereDoesntHave('memberships');

                            if ($record?->user_id) {
                                $query->orWhereKey($record->user_id);
                            }
                        })
                        ->orderBy('name')
                        ->limit(50)
                        ->get(['id', 'name', 'email'])
                        ->mapWithKeys(fn (User $user): array => [$user->id => "{$user->name} ({$user->email})"])
                        ->all();
                })
                ->getOptionLabelUsing(function ($value): ?string {
                    $user = User::query()->whereKey($value)->first(['name', 'email']);

                    return $user ? "{$user->name} ({$user->email})" : null;
                })
                ->searchable()
                ->preload()
                ->required()
                ->disabledOn('edit')
                ->createOptionForm([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('email')->email()->required()->unique('users', 'email')->maxLength(255),
                    TextInput::make('password')->password()->revealable()->required()->minLength(8),
                ])
                ->createOptionUsing(fn (array $data): int => User::create([
                    'name' => $data['name'],
                    'email' => mb_strtolower($data['email']),
                    'password' => Hash::make($data['password']),
                ])->id)
                ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('school_id', Filament::getTenant()->id)),
            Select::make('role')
                ->options(collect(MembershipRole::cases())->mapWithKeys(fn (MembershipRole $role): array => [$role->value => $role->label()])->all())
                ->required(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('user.name')->label('Name')->searchable()->sortable(),
            TextColumn::make('user.email')->searchable(),
            TextColumn::make('role')->badge()->formatStateUsing(fn ($state): string => $state instanceof MembershipRole ? $state->label() : (string) $state),
            IconColumn::make('is_active')->boolean(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolMemberships::route('/'),
            'create' => Pages\CreateSchoolMembership::route('/create'),
            'edit' => Pages\EditSchoolMembership::route('/{record}/edit'),
        ];
    }
}
