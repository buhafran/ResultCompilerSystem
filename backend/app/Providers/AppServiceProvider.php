<?php

namespace App\Providers;

use App\Models\School;
use App\Services\SchoolOwnershipGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        RouteBinding::register();
        SchoolOwnershipGuard::register();
    }
}

final class RouteBinding
{
    public static function register(): void
    {
        \Illuminate\Support\Facades\Route::bind('school', fn (string $value) => School::query()
            ->where('slug', $value)
            ->where('is_active', true)
            ->firstOrFail());
    }
}
