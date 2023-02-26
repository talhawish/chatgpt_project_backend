<?php

namespace App\Providers;


use Filament\Facades\Filament;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Yepsua\Filament\Themes\Facades\FilamentThemes;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('path.public', function () {
            return base_path() . '/public_html';
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        RateLimiter::for('generatePost', function ($job) {
            return Limit::perMinute(3)->by($job->data['website_id']);
        });

        RateLimiter::for('generatePostContent', function ($job) {
            return Limit::perMinute(5)->by($job->postId);
        });




        FilamentThemes::register(function ($path) {

            Filament::registerViteTheme('resources/css/filament.css');
            // Using Vite:
            return app(Vite::class)('resources/' . $path);
            // Using Mix:
            // return app(\Illuminate\Foundation\Mix::class)($path);
            // Using asset()
            // return asset($path);



        });
    }
}