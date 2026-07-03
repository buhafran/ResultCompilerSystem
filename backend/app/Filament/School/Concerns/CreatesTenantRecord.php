<?php

namespace App\Filament\School\Concerns;

use Filament\Facades\Filament;

trait CreatesTenantRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant, 404, 'School context is required.');

        $data['school_id'] = $tenant->getKey();

        return $data;
    }
}
