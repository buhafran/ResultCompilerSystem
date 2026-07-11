<?php

namespace App\Filament\Platform\Widgets;

use App\Models\ResultPublication;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverviewStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeSchools = School::query()->where('is_active', true)->count();
        $activeStudents = Student::query()->where('is_active', true)->count();
        $schoolUsers = User::query()->whereHas('memberships', fn ($query) => $query->where('is_active', true))->count();
        $releasedResults = ResultPublication::query()->where('status', 'released')->count();

        return [
            Stat::make('Active schools', number_format($activeSchools))
                ->description('Schools currently enabled')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
            Stat::make('Active students', number_format($activeStudents))
                ->description('Across all active schools')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('School users', number_format($schoolUsers))
                ->description('Users with active school access')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Released results', number_format($releasedResults))
                ->description('Total released publication versions')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('warning'),
        ];
    }
}
