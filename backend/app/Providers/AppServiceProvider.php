<?php

namespace App\Providers;

use App\Models\School;
use App\Services\SchoolOwnershipGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        if (app()->isProduction()) {
	    URL::forceRootUrl(config('app.url'));
	    URL::forceScheme('https');           
        }

        RouteBinding::register();
        SchoolOwnershipGuard::register();
      
            // Define the missing 'api' rate limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        
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
