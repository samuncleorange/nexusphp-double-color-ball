<?php

namespace NexusPlugin\DoubleColorBall\Repositories;

use App\Models\BonusLogs;
use App\Models\User;
use App\Repositories\BaseRepository;
use NexusPlugin\DoubleColorBall\Models\Period;
use NexusPlugin\DoubleColorBall\Models\Ticket;
use NexusPlugin\DoubleColorBall\Services\ProvablyFairService;
use NexusPlugin\DoubleColorBall\Services\PrizeCalculator;
use Illuminate\Support\Facades\Log;
use Nexus\Database\NexusDB;

/**
 * Draw Repository
 * 
 * Handles the lottery drawing process.
 */
class DrawRepository extends BaseRepository
{
    protected ProvablyFairService $provablyFairService;
    protected PrizeCalculator $prizeCalculator;
    protected PeriodRepository $periodRepository;
    protected TicketRepository $ticketRepository;

    public function __construct()
    {
        parent::__construct();
        $this->provablyFairService = new ProvablyFairService();
        $this->prizeCalculator = new PrizeCalculator();
        $this->periodRepository = new PeriodRepository();
        $this->ticketRepository = new TicketRepository();
    }

    /**
     * Execute lottery draw for a period.
     *
     * @param int $periodId
     * @return array Draw result
     * @throws \Exception
     */
    public function executeDraw(int $periodId): array
    {
        $period = Period::find($periodId);

        if (!$period) {
            throw new \InvalidArgumentException('Period not found.');
        }

        if ($period->status === Period::STATUS_DRAWN) {
            throw new \LogicException('Period already drawn.');
        }

        Log::info("Starting draw for period {$period->period_code}");

        // Get Bitcoin block hash
        $blockInfo = $this->provablyFairService->getLatestBitcoinBlock();
        if (!$blockInfo || !$blockInfo['hash']) {
            throw new \RuntimeException('Failed to get Bitcoin block hash.');
        }

        Log::info('Bitcoin block info', $blockInfo);

        // Generate winning numbers
        $config = config('nexus_plugin_dcb.game_rules');
        $winningNumbers = $this->provablyFairService->generateWinningNumbers(
            $blockInfo['hash'],
            $period->period_code,
            $config['red_ball_count'],
            $config['red_ball_max'],
            $config['blue_ball_count'],
            $config['blue_ball_max']
        );

        Log::info('Winning numbers generated', $winningNumbers);

        // Get all tickets for this period
        $tickets = $this->ticketRepository->getPeriodTickets($periodId);

        // Calculate winners
        $winnersByLevel = [];
        $ticketWinInfo = [];

        foreach ($tickets as $ticket) {
            $winLevel = $this->prizeCalculator->calculateWinLevel(
                $ticket->red_balls,
                $ticket->blue_balls,
                $winningNumbers['red'],
                $winningNumbers['blue']
            );

            if (!isset($winnersByLevel[$winLevel])) {
                $winnersByLevel[$winLevel] = 0;
            }
            $winnersByLevel[$winLevel]++;

            $ticketWinInfo[$ticket->id] = [
                'level' => $winLevel,
                'user_id' => $ticket->user_id,
            ];
        }

        Log::info('Winners by level', $winnersByLevel);

        // Calculate prizes
        $prizeDetails = $this->prizeCalculator->calculateAllPrizes(
            $winnersByLevel,
            $period->prize_pool
        );

        Log::info('Prize details', $prizeDetails);

        // Distribute prizes and update tickets
        $this->distributePrizes($ticketWinInfo, $prizeDetails, $period);

        // Update period
        $this->periodRepository->markAsDrawn(
            $periodId,
            $winningNumbers['red'],
            $winningNumbers['blue'],
            $blockInfo['hash'],
            $blockInfo['height'],
            $prizeDetails
        );

        // Handle rollover to next period
        if (isset($prizeDetails['rollover']) && $prizeDetails['rollover'] > 0) {
            $this->handleRollover($prizeDetails['rollover']);
        }

        Log::info("Draw completed for period {$period->period_code}");

        return [
            'period_code' => $period->period_code,
            'winning_numbers' => $winningNumbers,
            'block_hash' => $blockInfo['hash'],
            'block_height' => $blockInfo['height'],
            'winners_by_level' => $winnersByLevel,
            'prize_details' => $prizeDetails,
        ];
    }

    /**
     * Distribute prizes to winners.
     *
     * @param array $ticketWinInfo
     * @param array $prizeDetails
     * @param Period $period
     */
    protected function distributePrizes(array $ticketWinInfo, array $prizeDetails, Period $period): void
    {
        foreach ($ticketWinInfo as $ticketId => $info) {
            $winLevel = $info['level'];
            $userId = $info['user_id'];

            if ($winLevel === 0) {
                continue; // No win
            }

            $prizeAmount = $prizeDetails[$winLevel]['per_winner'] ?? 0;

            if ($prizeAmount <= 0) {
                continue;
            }

            // Update ticket
            $this->ticketRepository->updateWinningInfo($ticketId, $winLevel, $prizeAmount);

            // Award bonus to user
            $this->awardPrize($userId, $prizeAmount, $period->period_code, $winLevel);

            // Send notification
            $this->sendWinNotification($userId, $period->period_code, $winLevel, $prizeAmount);
        }
    }

    /**
     * Award prize to user.
     *
     * @param int $userId
     * @param float $amount
     * @param string $periodCode
     * @param int $winLevel
     */
    protected function awardPrize(int $userId, float $amount, string $periodCode, int $winLevel): void
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                Log::error("User not found: {$userId}");
                return;
            }

            NexusDB::transaction(function () use ($user, $amount, $periodCode, $winLevel) {
                $oldBonus = $user->seedbonus;
                $newBonus = bcadd($oldBonus, $amount, 2);

                // Update user bonus
                $affectedRows = NexusDB::table('users')
                    ->where('id', $user->id)
                    ->where('seedbonus', $oldBonus)
                    ->update(['seedbonus' => $newBonus]);

                if ($affectedRows != 1) {
                    throw new \RuntimeException('Failed to update user bonus.');
                }

                // Create bonus log
                BonusLogs::add(
                    $user->id,
                    $oldBonus,
                    $amount,
                    $newBonus,
                    sprintf('Period: %s, Level: %d', $periodCode, $winLevel),
                    TicketRepository::BUSINESS_TYPE_WIN_DCB_PRIZE
                );

                clear_user_cache($user->id, $user->passkey);
            });

            Log::info("Prize awarded", [
                'user_id' => $userId,
                'amount' => $amount,
                'period' => $periodCode,
                'level' => $winLevel,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to award prize: " . $e->getMessage(), [
                'user_id' => $userId,
                'amount' => $amount,
            ]);
        }
    }

    /**
     * Send win notification to user.
     *
     * @param int $userId
     * @param string $periodCode
     * @param int $winLevel
     * @param float $amount
     */
    protected function sendWinNotification(int $userId, string $periodCode, int $winLevel, float $amount): void
    {
        if (!config('nexus_plugin_dcb.notifications.send_pm')) {
            return;
        }

        try {
            $subject = nexus_trans('dcb::dcb.notification.win_subject');
            $body = nexus_trans('dcb::dcb.notification.win_body', [
                'period' => $periodCode,
                'level' => $winLevel,
                'amount' => number_format($amount, 2),
            ]);

            // Send PM using NexusPHP's message system
            \App\Models\Message::create([
                'sender' => 0, // System message
                'receiver' => $userId,
                'subject' => $subject,
                'msg' => $body,
                'added' => now(),
            ]);

            Log::info("Win notification sent", [
                'user_id' => $userId,
                'period' => $periodCode,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send notification: " . $e->getMessage());
        }
    }

    /**
     * Handle prize pool rollover to next period.
     *
     * @param float $rolloverAmount
     */
    protected function handleRollover(float $rolloverAmount): void
    {
        // Get or create next period
        $nextPeriod = $this->periodRepository->getCurrentOpenPeriod();

        if (!$nextPeriod) {
            $nextPeriod = $this->periodRepository->createPeriod();
        }

        // Add rollover to next period's prize pool
        $this->periodRepository->addToPrizePool($nextPeriod->id, $rolloverAmount);

        Log::info("Rollover added to next period", [
            'amount' => $rolloverAmount,
            'next_period' => $nextPeriod->period_code,
        ]);
    }

    /**
     * Close current period and prepare for draw.
     *
     * @return Period|null
     */
    public function closeCurrentPeriod(): ?Period
    {
        $currentPeriod = $this->periodRepository->getCurrentOpenPeriod();

        if (!$currentPeriod) {
            return null;
        }

        $this->periodRepository->closePeriod($currentPeriod->id);

        Log::info("Period closed", ['period_code' => $currentPeriod->period_code]);

        return $currentPeriod;
    }
}
