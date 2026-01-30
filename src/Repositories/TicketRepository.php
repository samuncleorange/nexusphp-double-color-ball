<?php

namespace NexusPlugin\DoubleColorBall\Repositories;

use App\Models\BonusLogs;
use App\Repositories\BaseRepository;
use App\Repositories\BonusRepository;
use NexusPlugin\DoubleColorBall\Models\Period;
use NexusPlugin\DoubleColorBall\Models\Ticket;
use Nexus\Database\NexusDB;
use Illuminate\Support\Facades\Log;

/**
 * Ticket Repository
 * 
 * Handles ticket purchase and management.
 */
class TicketRepository extends BaseRepository
{
    // Define custom business types for double color ball
    // Using 100-199 range to avoid conflicts
    const BUSINESS_TYPE_BUY_DCB_TICKET = 100;
    const BUSINESS_TYPE_WIN_DCB_PRIZE = 1100;

    protected BonusRepository $bonusRepository;
    protected PeriodRepository $periodRepository;

    public function __construct()
    {
        parent::__construct();
        $this->bonusRepository = new BonusRepository();
        $this->periodRepository = new PeriodRepository();
    }

    /**
     * Purchase a lottery ticket.
     *
     * @param int $userId
     * @param int $periodId
     * @param array $redBalls
     * @param array $blueBalls
     * @return Ticket
     * @throws \Exception
     */
    public function purchaseTicket(
        int $userId,
        int $periodId,
        array $redBalls,
        array $blueBalls
    ): Ticket {
        // Validate period
        $period = Period::find($periodId);
        if (!$period || !$period->isOpen()) {
            throw new \LogicException('Period is not open for ticket sales.');
        }

        // Validate numbers
        $this->validateNumbers($redBalls, $blueBalls);

        // Get ticket price
        $price = config('nexus_plugin_dcb.price_per_ticket');

        // Check purchase limit
        $userTicketCount = Ticket::where('period_id', $periodId)
            ->where('user_id', $userId)
            ->count();

        $maxTickets = config('nexus_plugin_dcb.max_tickets_per_user');
        if ($userTicketCount >= $maxTickets) {
            throw new \LogicException("Maximum {$maxTickets} tickets per period.");
        }

        $ticket = null;

        // Use transaction to ensure atomicity
        NexusDB::transaction(function () use (
            $userId,
            $periodId,
            $redBalls,
            $blueBalls,
            $price,
            $period,
            &$ticket
        ) {
            // Deduct bonus
            $user = \App\Models\User::find($userId);
            $this->bonusRepository->consumeUserBonus(
                $user,
                $price,
                self::BUSINESS_TYPE_BUY_DCB_TICKET,
                sprintf('Period: %s', $period->period_code)
            );

            // Create ticket
            $ticket = Ticket::create([
                'period_id' => $periodId,
                'user_id' => $userId,
                'red_balls' => $redBalls,
                'blue_balls' => $blueBalls,
                'cost' => $price,
                'win_level' => 0,
                'win_bonus' => 0,
            ]);

            // Add to prize pool
            $this->periodRepository->addToPrizePool($periodId, $price);

            Log::info('Ticket purchased', [
                'user_id' => $userId,
                'period_id' => $periodId,
                'ticket_id' => $ticket->id,
                'cost' => $price,
            ]);
        });

        return $ticket;
    }

    /**
     * Validate selected numbers.
     *
     * @param array $redBalls
     * @param array $blueBalls
     * @throws \InvalidArgumentException
     */
    protected function validateNumbers(array $redBalls, array $blueBalls): void
    {
        $config = config('nexus_plugin_dcb.game_rules');

        // Check red balls count
        if (count($redBalls) !== $config['red_ball_count']) {
            throw new \InvalidArgumentException(
                sprintf('Must select exactly %d red balls.', $config['red_ball_count'])
            );
        }

        // Check blue balls count
        if (count($blueBalls) !== $config['blue_ball_count']) {
            throw new \InvalidArgumentException(
                sprintf('Must select exactly %d blue balls.', $config['blue_ball_count'])
            );
        }

        // Check red balls range and uniqueness
        $uniqueRed = array_unique($redBalls);
        if (count($uniqueRed) !== count($redBalls)) {
            throw new \InvalidArgumentException('Red balls must be unique.');
        }

        foreach ($redBalls as $ball) {
            if ($ball < 1 || $ball > $config['red_ball_max']) {
                throw new \InvalidArgumentException(
                    sprintf('Red ball must be between 1 and %d.', $config['red_ball_max'])
                );
            }
        }

        // Check blue balls range and uniqueness
        $uniqueBlue = array_unique($blueBalls);
        if (count($uniqueBlue) !== count($blueBalls)) {
            throw new \InvalidArgumentException('Blue balls must be unique.');
        }

        foreach ($blueBalls as $ball) {
            if ($ball < 1 || $ball > $config['blue_ball_max']) {
                throw new \InvalidArgumentException(
                    sprintf('Blue ball must be between 1 and %d.', $config['blue_ball_max'])
                );
            }
        }
    }

    /**
     * Get user's tickets for a period.
     *
     * @param int $userId
     * @param int $periodId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTickets(int $userId, int $periodId)
    {
        return Ticket::where('user_id', $userId)
            ->where('period_id', $periodId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all user's tickets with pagination.
     *
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllUserTickets(int $userId, int $perPage = 20)
    {
        return Ticket::where('user_id', $userId)
            ->with('period')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all tickets for a period.
     *
     * @param int $periodId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPeriodTickets(int $periodId)
    {
        return Ticket::where('period_id', $periodId)->get();
    }

    /**
     * Update ticket with winning information.
     *
     * @param int $ticketId
     * @param int $winLevel
     * @param float $winBonus
     * @return bool
     */
    public function updateWinningInfo(int $ticketId, int $winLevel, float $winBonus): bool
    {
        return Ticket::where('id', $ticketId)
            ->update([
                'win_level' => $winLevel,
                'win_bonus' => $winBonus,
            ]) > 0;
    }

    /**
     * Get user's winning tickets.
     *
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserWinningTickets(int $userId, int $perPage = 20)
    {
        return Ticket::where('user_id', $userId)
            ->where('win_level', '>', 0)
            ->with('period')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get user's total winnings.
     *
     * @param int $userId
     * @return float
     */
    public function getUserTotalWinnings(int $userId): float
    {
        return Ticket::where('user_id', $userId)
            ->sum('win_bonus');
    }

    /**
     * Get user's total spending.
     *
     * @param int $userId
     * @return float
     */
    public function getUserTotalSpending(int $userId): float
    {
        return Ticket::where('user_id', $userId)
            ->sum('cost');
    }
}
