<?php

namespace NexusPlugin\DoubleColorBall\Console\Commands;

use Illuminate\Console\Command;
use NexusPlugin\DoubleColorBall\Repositories\DrawRepository;
use NexusPlugin\DoubleColorBall\Repositories\PeriodRepository;
use Illuminate\Support\Facades\Log;

/**
 * Draw Lottery Command
 * 
 * Execute lottery draw for closed periods.
 */
class DrawLottery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcb:draw 
                            {--period= : Specific period ID to draw}
                            {--force : Force draw even if period is already drawn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute Double Color Ball lottery draw';

    protected DrawRepository $drawRepository;
    protected PeriodRepository $periodRepository;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->drawRepository = new DrawRepository();
        $this->periodRepository = new PeriodRepository();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Double Color Ball draw...');

        try {
            $periodId = $this->option('period');

            if ($periodId) {
                // Draw specific period
                $this->drawSpecificPeriod((int) $periodId);
            } else {
                // Draw all ready periods
                $this->drawReadyPeriods();
            }

            $this->info('Draw completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Draw failed: ' . $e->getMessage());
            Log::error('DCB Draw failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Draw a specific period.
     *
     * @param int $periodId
     */
    protected function drawSpecificPeriod(int $periodId): void
    {
        $this->info("Drawing period ID: {$periodId}");

        $result = $this->drawRepository->executeDraw($periodId);

        $this->displayDrawResult($result);
    }

    /**
     * Draw all periods ready for drawing.
     */
    protected function drawReadyPeriods(): void
    {
        // First, close current open period
        $closedPeriod = $this->drawRepository->closeCurrentPeriod();
        
        if ($closedPeriod) {
            $this->info("Closed current period: {$closedPeriod->period_code}");
        }

        // Get all periods ready for draw
        $periods = $this->periodRepository->getPeriodsReadyForDraw();

        if ($periods->isEmpty()) {
            $this->warn('No periods ready for drawing.');
            return;
        }

        $this->info("Found {$periods->count()} period(s) ready for drawing.");

        foreach ($periods as $period) {
            $this->info("Drawing period: {$period->period_code}");

            try {
                $result = $this->drawRepository->executeDraw($period->id);
                $this->displayDrawResult($result);
            } catch (\Exception $e) {
                $this->error("Failed to draw period {$period->period_code}: " . $e->getMessage());
                Log::error("Failed to draw period {$period->period_code}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        // Create next period
        $this->createNextPeriod();
    }

    /**
     * Display draw result.
     *
     * @param array $result
     */
    protected function displayDrawResult(array $result): void
    {
        $this->info("Period: {$result['period_code']}");
        $this->info("Winning Red Balls: " . implode(', ', $result['winning_numbers']['red']));
        $this->info("Winning Blue Balls: " . implode(', ', $result['winning_numbers']['blue']));
        $this->info("Block Hash: {$result['block_hash']}");
        $this->info("Block Height: {$result['block_height']}");

        $this->table(
            ['Level', 'Winners', 'Per Winner', 'Total'],
            collect($result['prize_details'])
                ->filter(fn($detail, $key) => $key !== 'rollover')
                ->map(fn($detail, $level) => [
                    "Level {$level}",
                    $detail['count'],
                    number_format($detail['per_winner'], 2),
                    number_format($detail['total'], 2),
                ])
                ->values()
                ->toArray()
        );

        if (isset($result['prize_details']['rollover']) && $result['prize_details']['rollover'] > 0) {
            $this->warn("Rollover: " . number_format($result['prize_details']['rollover'], 2));
        }
    }

    /**
     * Create next period.
     */
    protected function createNextPeriod(): void
    {
        try {
            $nextPeriod = $this->periodRepository->createPeriod();
            $this->info("Created next period: {$nextPeriod->period_code}");
        } catch (\Exception $e) {
            $this->error("Failed to create next period: " . $e->getMessage());
        }
    }
}
