<?php

namespace NexusPlugin\DoubleColorBall;

use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Double Color Ball Filament Plugin
 * 
 * Registers Filament resources for the admin panel.
 */
class DoubleColorBallPlugin implements Plugin
{
    public function getId(): string
    {
        return 'double-color-ball';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            Filament\Resources\PeriodResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }
}
