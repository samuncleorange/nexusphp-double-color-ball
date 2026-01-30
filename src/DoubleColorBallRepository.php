<?php

namespace NexusPlugin\DoubleColorBall;

use Nexus\Plugin\BasePlugin;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Double Color Ball Repository
 * 
 * Main plugin class extending NexusPHP BasePlugin.
 */
class DoubleColorBallRepository extends BasePlugin
{
    /**
     * Plugin ID
     */
    const ID = 'double-color-ball';

    /**
     * Plugin version
     */
    const VERSION = '0.1.0';

    /**
     * Compatible NexusPHP version
     */
    const COMPATIBLE_NP_VERSION = '1.7.0';

    /**
     * Install the plugin.
     */
    public function install(): void
    {
        Log::info('Installing Double Color Ball plugin...');

        try {
            // Run migrations
            $migrationPath = __DIR__ . '/../database/migrations';
            $this->runMigrations($migrationPath);

            // Publish config
            Artisan::call('vendor:publish', [
                '--tag' => 'dcb-config',
                '--force' => true,
            ]);

            // Publish assets
            Artisan::call('vendor:publish', [
                '--tag' => 'dcb-assets',
                '--force' => true,
            ]);

            // Create initial period
            $this->createInitialPeriod();

            Log::info('Double Color Ball plugin installed successfully');
        } catch (\Exception $e) {
            Log::error('Failed to install Double Color Ball plugin: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Uninstall the plugin.
     */
    public function uninstall(): void
    {
        Log::info('Uninstalling Double Color Ball plugin...');

        try {
            // Rollback migrations
            $migrationPath = __DIR__ . '/../database/migrations';
            $this->runMigrations($migrationPath, true);

            Log::info('Double Color Ball plugin uninstalled successfully');
        } catch (\Exception $e) {
            Log::error('Failed to uninstall Double Color Ball plugin: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Boot the plugin.
     */
    public function boot(): void
    {
        Log::info('Double Color Ball plugin booted', [
            'version' => self::VERSION,
        ]);

        // Register custom business types for bonus logs
        $this->registerBonusBusinessTypes();
    }

    /**
     * Create initial period.
     */
    protected function createInitialPeriod(): void
    {
        try {
            $periodRepo = new Repositories\PeriodRepository();
            $currentPeriod = $periodRepo->getCurrentOpenPeriod();

            if (!$currentPeriod) {
                $period = $periodRepo->createPeriod();
                Log::info('Created initial period', ['period_code' => $period->period_code]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create initial period: ' . $e->getMessage());
        }
    }

    /**
     * Register custom business types for bonus logs.
     */
    protected function registerBonusBusinessTypes(): void
    {
        // Add custom business types to BonusLogs
        if (class_exists(\App\Models\BonusLogs::class)) {
            \App\Models\BonusLogs::$businessTypes[Repositories\TicketRepository::BUSINESS_TYPE_BUY_DCB_TICKET] = [
                'text' => 'Buy Double Color Ball ticket'
            ];
            \App\Models\BonusLogs::$businessTypes[Repositories\TicketRepository::BUSINESS_TYPE_WIN_DCB_PRIZE] = [
                'text' => 'Win Double Color Ball prize'
            ];
        }
    }

    /**
     * Get plugin name.
     */
    public function getName(): string
    {
        return nexus_trans('dcb::dcb.name');
    }

    /**
     * Get plugin description.
     */
    public function getDescription(): string
    {
        return nexus_trans('dcb::dcb.description');
    }

    /**
     * Get plugin ID.
     */
    public function getId(): string
    {
        return self::ID;
    }
}
