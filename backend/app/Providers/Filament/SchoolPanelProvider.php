<?php

namespace App\Providers\Filament;

use App\Models\School;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\School\Pages\Dashboard;
use App\Filament\School\Widgets\ClassPerformanceChart;
use App\Filament\School\Widgets\ResultStatusChart;
use App\Filament\School\Widgets\SchoolOverviewStats;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SchoolPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('school')
            ->path('admin')
            ->login()
            ->databaseTransactions()
            ->unsavedChangesAlerts()
            ->brandName('School Result Administration')
            ->colors(['primary' => Color::Emerald])
            ->tenant(School::class, slugAttribute: 'slug', ownershipRelationship: 'school')
            ->tenantRoutePrefix('school')
            ->discoverResources(in: app_path('Filament/School/Resources'), for: 'App\\Filament\\School\\Resources')
            ->pages([Dashboard::class])
            ->widgets([
                SchoolOverviewStats::class,
                ResultStatusChart::class,
                ClassPerformanceChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class], isPersistent: true);
    }
}
