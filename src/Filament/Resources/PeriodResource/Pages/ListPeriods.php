<?php

namespace NexusPlugin\DoubleColorBall\Filament\Resources\PeriodResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use NexusPlugin\DoubleColorBall\Filament\Resources\PeriodResource;

class ListPeriods extends ListRecords
{
    protected static string $resource = PeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
