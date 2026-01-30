<?php

namespace NexusPlugin\DoubleColorBall\Repositories;

use App\Repositories\BaseRepository;
use NexusPlugin\DoubleColorBall\Models\Period;
use Carbon\Carbon;

/**
 * Period Repository
 * 
 * Handles period-related business logic.
 */
class PeriodRepository extends BaseRepository
{
    /**
     * Get the current open period.
     *
     * @return Period|null
     */
    public function getCurrentOpenPeriod(): ?Period
    {
        return Period::where('status', Period::STATUS_OPEN)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Create a new period.
     *
     * @param string|null $periodCode Optional custom period code
     * @return Period
     */
    public function createPeriod(?string $periodCode = null): Period
    {
        if (!$periodCode) {
            $periodCode = $this->generatePeriodCode();
        }

        return Period::create([
            'period_code' => $periodCode,
            'status' => Period::STATUS_OPEN,
            'prize_pool' => 0,
        ]);
    }

    /**
     * Generate period code in format YYYYMMDDNN.
     *
     * @return string
     */
    public function generatePeriodCode(): string
    {
        $date = now()->format('Ymd');
        
        // Find the last period code for today
        $lastPeriod = Period::where('period_code', 'like', $date . '%')
            ->orderBy('period_code', 'desc')
            ->first();

        if ($lastPeriod) {
            $lastNumber = (int) substr($lastPeriod->period_code, -2);
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '01';
        }

        return $date . $newNumber;
    }

    /**
     * Close the current period (stop ticket sales).
     *
     * @param int $periodId
     * @return bool
     */
    public function closePeriod(int $periodId): bool
    {
        return Period::where('id', $periodId)
            ->where('status', Period::STATUS_OPEN)
            ->update(['status' => Period::STATUS_CLOSED]) > 0;
    }

    /**
     * Mark period as drawn.
     *
     * @param int $periodId
     * @param array $redBalls
     * @param array $blueBalls
     * @param string $blockHash
     * @param int $blockHeight
     * @param array $winDetails
     * @return bool
     */
    public function markAsDrawn(
        int $periodId,
        array $redBalls,
        array $blueBalls,
        string $blockHash,
        int $blockHeight,
        array $winDetails
    ): bool {
        return Period::where('id', $periodId)
            ->update([
                'status' => Period::STATUS_DRAWN,
                'red_balls' => $redBalls,
                'blue_balls' => $blueBalls,
                'block_hash' => $blockHash,
                'block_height' => $blockHeight,
                'win_details' => $winDetails,
                'opened_at' => now(),
            ]) > 0;
    }

    /**
     * Add to prize pool.
     *
     * @param int $periodId
     * @param float $amount
     * @return bool
     */
    public function addToPrizePool(int $periodId, float $amount): bool
    {
        return Period::where('id', $periodId)
            ->increment('prize_pool', $amount) > 0;
    }

    /**
     * Get periods ready for drawing.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPeriodsReadyForDraw()
    {
        return Period::where('status', Period::STATUS_CLOSED)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get period by code.
     *
     * @param string $periodCode
     * @return Period|null
     */
    public function getByCode(string $periodCode): ?Period
    {
        return Period::where('period_code', $periodCode)->first();
    }

    /**
     * Get recent periods.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentPeriods(int $limit = 10)
    {
        return Period::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get drawn periods with pagination.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getDrawnPeriods(int $perPage = 20)
    {
        return Period::where('status', Period::STATUS_DRAWN)
            ->orderBy('opened_at', 'desc')
            ->paginate($perPage);
    }
}
