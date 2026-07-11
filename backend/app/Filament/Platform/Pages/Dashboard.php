<?php

namespace App\Filament\Platform\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return ['md' => 2, 'xl' => 2];
    }
}
