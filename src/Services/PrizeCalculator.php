<?php

namespace NexusPlugin\DoubleColorBall\Services;

use NexusPlugin\DoubleColorBall\Models\Ticket;

/**
 * Prize Calculator Service
 * 
 * Calculates winning levels and prize amounts.
 */
class PrizeCalculator
{
    /**
     * Calculate winning level for a ticket.
     *
     * @param array $ticketRed User's selected red balls
     * @param array $ticketBlue User's selected blue balls
     * @param array $winningRed Winning red balls
     * @param array $winningBlue Winning blue balls
     * @return int Winning level (0 = no win)
     */
    public function calculateWinLevel(
        array $ticketRed,
        array $ticketBlue,
        array $winningRed,
        array $winningBlue
    ): int {
        $redMatches = count(array_intersect($ticketRed, $winningRed));
        $blueMatches = count(array_intersect($ticketBlue, $winningBlue));

        $config = config('nexus_plugin_dcb.game_rules');
        $totalRed = $config['red_ball_count'];
        $totalBlue = $config['blue_ball_count'];

        // Level 1: All red + all blue
        if ($redMatches === $totalRed && $blueMatches === $totalBlue) {
            return 1;
        }

        // Level 2: All red OR (all-1 red + all blue)
        if ($redMatches === $totalRed || 
            ($redMatches === $totalRed - 1 && $blueMatches === $totalBlue)) {
            return 2;
        }

        // Level 3: (all-1 red) OR (all-2 red + all blue)
        if ($redMatches === $totalRed - 1 || 
            ($redMatches === $totalRed - 2 && $blueMatches === $totalBlue)) {
            return 3;
        }

        // Level 4: (all-2 red) OR (all-3 red + all blue)
        if ($redMatches === $totalRed - 2 || 
            ($redMatches === $totalRed - 3 && $blueMatches === $totalBlue)) {
            return 4;
        }

        // Level 5: (all-3 red) OR any other partial match
        if ($redMatches === $totalRed - 3 || 
            ($redMatches >= $totalRed - 4 && $blueMatches > 0)) {
            return 5;
        }

        return 0; // No win
    }

    /**
     * Calculate prize amount for a winning level.
     *
     * @param int $level Winning level
     * @param float $prizePool Total prize pool
     * @param int $winnersCount Number of winners at this level
     * @return float Prize amount per winner
     */
    public function calculatePrizeAmount(int $level, float $prizePool, int $winnersCount): float
    {
        if ($level === 0 || $winnersCount === 0) {
            return 0;
        }

        $allocation = config("nexus_plugin_dcb.prize_allocation.$level");

        if (!$allocation) {
            return 0;
        }

        if ($allocation['type'] === 'fixed') {
            return (float) $allocation['value'];
        }

        if ($allocation['type'] === 'ratio') {
            $totalForLevel = $prizePool * $allocation['value'];
            $perWinner = $totalForLevel / $winnersCount;

            // Ensure minimum prize
            $minimum = $allocation['min'] ?? 0;
            return max($perWinner, $minimum);
        }

        return 0;
    }

    /**
     * Calculate total prizes for all winning levels.
     *
     * @param array $winnersByLevel Array of [level => count]
     * @param float $prizePool Total prize pool
     * @return array Array of [level => ['count' => int, 'per_winner' => float, 'total' => float]]
     */
    public function calculateAllPrizes(array $winnersByLevel, float $prizePool): array
    {
        $result = [];
        $totalPaid = 0;

        foreach ($winnersByLevel as $level => $count) {
            if ($level === 0 || $count === 0) {
                continue;
            }

            $perWinner = $this->calculatePrizeAmount($level, $prizePool, $count);
            $total = $perWinner * $count;

            $result[$level] = [
                'count' => $count,
                'per_winner' => $perWinner,
                'total' => $total,
            ];

            $totalPaid += $total;
        }

        // Add rollover information
        $result['rollover'] = max(0, $prizePool - $totalPaid);

        return $result;
    }

    /**
     * Count winners by level for a period.
     *
     * @param array $tickets Collection of tickets
     * @param array $winningRed Winning red balls
     * @param array $winningBlue Winning blue balls
     * @return array Array of [level => count]
     */
    public function countWinnersByLevel(array $tickets, array $winningRed, array $winningBlue): array
    {
        $winnersByLevel = [];

        foreach ($tickets as $ticket) {
            $level = $this->calculateWinLevel(
                $ticket['red_balls'] ?? [],
                $ticket['blue_balls'] ?? [],
                $winningRed,
                $winningBlue
            );

            if (!isset($winnersByLevel[$level])) {
                $winnersByLevel[$level] = 0;
            }

            $winnersByLevel[$level]++;
        }

        return $winnersByLevel;
    }

    /**
     * Get winning level description.
     *
     * @param int $level
     * @return string
     */
    public function getWinLevelDescription(int $level): string
    {
        if ($level === 0) {
            return nexus_trans('dcb::dcb.win_level.no_win');
        }

        return nexus_trans("dcb::dcb.win_level.level_{$level}");
    }
}
