<?php

namespace NexusPlugin\DoubleColorBall;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use NexusPlugin\DoubleColorBall\Console\Commands\DrawLottery;

/**
 * Double Color Ball Service Provider
 * 
 * Registers plugin services, routes, views, and scheduled tasks.
 */
class DoubleColorBallServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nexus_plugin_dcb.php',
            'nexus_plugin_dcb'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register routes
        $this->registerRoutes();

        // Register views
        $this->registerViews();

        // Register translations
        $this->registerTranslations();

        // Register migrations
        $this->registerMigrations();

        // Register commands
        $this->registerCommands();

        // Register scheduled tasks
        $this->registerScheduledTasks();

        // Publish assets
        $this->registerPublishing();
    }

    /**
     * Register plugin routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth.nexus:nexus-web'])
            ->prefix('plugin/php-double-color-ball')
            ->name('dcb.')
            ->group(function () {
                Route::get('/', [\NexusPlugin\DoubleColorBall\Http\Controllers\DoubleColorBallController::class, 'index'])
                    ->name('index');
                Route::post('/buy', [\NexusPlugin\DoubleColorBall\Http\Controllers\DoubleColorBallController::class, 'buy'])
                    ->name('buy');
                Route::get('/my-tickets', [\NexusPlugin\DoubleColorBall\Http\Controllers\DoubleColorBallController::class, 'myTickets'])
                    ->name('my-tickets');
                Route::get('/history', [\NexusPlugin\DoubleColorBall\Http\Controllers\DoubleColorBallController::class, 'history'])
                    ->name('history');
                Route::get('/verify', [\NexusPlugin\DoubleColorBall\Http\Controllers\DoubleColorBallController::class, 'verify'])
                    ->name('verify');
                Route::post('/verify', [\NexusPlugin\DoubleColorBall\Http\Controllers\DoubleColorBallController::class, 'doVerify'])
                    ->name('do-verify');
            });
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dcb');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'dcb');
    }

    /**
     * Register migrations.
     */
    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Register commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DrawLottery::class,
            ]);
        }
    }

    /**
     * Register scheduled tasks.
     */
    protected function registerScheduledTasks(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            $config = config('nexus_plugin_dcb.draw_schedule');
            $day = $config['day'] ?? 'sunday';
            $time = $config['time'] ?? '21:00';
            $timezone = $config['timezone'] ?? 'Asia/Shanghai';

            // Schedule weekly draw
            $dayMethod = \Illuminate\Support\Str::plural($day);
            $schedule->command('dcb:draw')
                ->$dayMethod()
                ->at($time)
                ->timezone($timezone)
                ->onSuccess(function () {
                    \Illuminate\Support\Facades\Log::info('DCB draw completed successfully');
                })
                ->onFailure(function () {
                    \Illuminate\Support\Facades\Log::error('DCB draw failed');
                });
        });
    }

    /**
     * Register publishing.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/nexus_plugin_dcb.php' => config_path('nexus_plugin_dcb.php'),
            ], 'dcb-config');

            // Publish views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/dcb'),
            ], 'dcb-views');

            // Publish assets
            $this->publishes([
                __DIR__ . '/../resources/assets' => public_path('vendor/dcb'),
            ], 'dcb-assets');
        }
    }
}
