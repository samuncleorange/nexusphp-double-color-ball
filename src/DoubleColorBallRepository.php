<?php

namespace NexusPlugin\DoubleColorBall;

use Nexus\Plugin\BasePlugin;

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
        do_log('Installing Double Color Ball plugin...');

        try {
            // Run migrations
            $migrationPath = __DIR__ . '/../database/migrations';
            $this->runMigrations($migrationPath);

            // Publish config
            if (function_exists('artisan')) {
                artisan('vendor:publish', [
                    '--tag' => 'dcb-config',
                    '--force' => true,
                ]);

                // Publish assets
                artisan('vendor:publish', [
                    '--tag' => 'dcb-assets',
                    '--force' => true,
                ]);
            }

            // Create initial period
            $this->createInitialPeriod();

            do_log('Double Color Ball plugin installed successfully');
        } catch (\Exception $e) {
            do_log('Failed to install Double Color Ball plugin: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Uninstall the plugin.
     */
    public function uninstall(): void
    {
        do_log('Uninstalling Double Color Ball plugin...');

        try {
            // Rollback migrations
            $migrationPath = __DIR__ . '/../database/migrations';
            $this->runMigrations($migrationPath, true);

            do_log('Double Color Ball plugin uninstalled successfully');
        } catch (\Exception $e) {
            do_log('Failed to uninstall Double Color Ball plugin: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Boot the plugin.
     */
    public function boot(): void
    {
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
                do_log('Created initial period: ' . $period->period_code);
            }
        } catch (\Exception $e) {
            do_log('Failed to create initial period: ' . $e->getMessage(), 'warning');
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
