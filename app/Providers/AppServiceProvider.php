<?php

namespace App\Providers;

use App\Services\NotificationBellService;
use App\Services\PendingReportsCounter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('partials.sidebar', function ($view) {
            $view->with(
                'pendingReportsSidebarCount',
                app(PendingReportsCounter::class)->countForSidebar()
            );
        });

        View::composer(['partials.sidebar', 'notifications._subnav'], function ($view) {
            $view->with(app(NotificationBellService::class)->composeData());
        });
        
        // Set timezone to Asia/Manila
        $timezone = env('APP_TIMEZONE', 'Asia/Manila');
        date_default_timezone_set($timezone);
        config(['app.timezone' => $timezone]);
    }
}
