<?php

namespace App\Filament\School\Resources;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class TenantManagedResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->when($tenant, fn (Builder $query): Builder => $query->where(
                $query->getModel()->qualifyColumn('school_id'),
                $tenant->getKey(),
            ));
    }

    public static function canViewAny(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant && (bool) auth()->user()?->isSchoolManager($tenant);
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny() && (int) $record->getAttribute('school_id') === (int) Filament::getTenant()?->getKey();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function canDeleteAny(): bool
    {
        return static::canViewAny();
    }
}
