<?php

namespace NexusPlugin\DoubleColorBall\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provably Fair Service
 * 
 * Implements provably fair algorithm using Bitcoin block hash.
 */
class ProvablyFairService
{
    /**
     * Get the latest Bitcoin block information.
     *
     * @return array{hash: string, height: int}|null
     */
    public function getLatestBitcoinBlock(): ?array
    {
        $config = config('nexus_plugin_dcb.bitcoin_api');

        if (!$config['enabled']) {
            Log::warning('Bitcoin API is disabled, using fallback random');
            return $this->getFallbackBlock();
        }

        try {
            $response = Http::timeout($config['timeout'])
                ->get($config['endpoint']);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'hash' => $data['hash'] ?? null,
                    'height' => $data['height'] ?? null,
                ];
            }

            Log::error('Failed to fetch Bitcoin block', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->getFallbackBlock();
        } catch (\Exception $e) {
            Log::error('Exception fetching Bitcoin block: ' . $e->getMessage());
            return $this->getFallbackBlock();
        }
    }

    /**
     * Generate winning numbers using provably fair algorithm.
     *
     * @param string $blockHash Bitcoin block hash
     * @param string $periodCode Period code
     * @param int $redCount Number of red balls to generate
     * @param int $redMax Maximum red ball number
     * @param int $blueCount Number of blue balls to generate
     * @param int $blueMax Maximum blue ball number
     * @return array{red: array, blue: array}
     */
    public function generateWinningNumbers(
        string $blockHash,
        string $periodCode,
        int $redCount,
        int $redMax,
        int $blueCount,
        int $blueMax
    ): array {
        // Generate seed using HMAC-SHA512
        $seed = hash_hmac('sha512', $blockHash, $periodCode);

        // Generate red balls
        $redBalls = $this->extractUniqueNumbers($seed, 0, $redCount, $redMax);
        sort($redBalls);

        // Generate blue balls
        $blueBalls = $this->extractUniqueNumbers($seed, $redCount, $blueCount, $blueMax);
        sort($blueBalls);

        return [
            'red' => $redBalls,
            'blue' => $blueBalls,
        ];
    }

    /**
     * Extract unique numbers from seed.
     *
     * @param string $seed Hexadecimal seed
     * @param int $offset Offset in seed to start from
     * @param int $count Number of unique numbers to extract
     * @param int $max Maximum number value
     * @return array
     */
    private function extractUniqueNumbers(string $seed, int $offset, int $count, int $max): array
    {
        $numbers = [];
        $position = $offset * 8; // Each number uses 8 hex chars (4 bytes)

        while (count($numbers) < $count) {
            // Get 8 hex characters (4 bytes)
            $hexChunk = substr($seed, $position % strlen($seed), 8);
            $position += 8;

            // Convert to integer
            $value = hexdec($hexChunk);

            // Map to range [1, max]
            $number = ($value % $max) + 1;

            // Only add if unique
            if (!in_array($number, $numbers)) {
                $numbers[] = $number;
            }

            // Prevent infinite loop
            if ($position > strlen($seed) * 10) {
                // Re-hash seed to get more randomness
                $seed = hash('sha512', $seed . $position);
                $position = 0;
            }
        }

        return $numbers;
    }

    /**
     * Verify winning numbers using the same algorithm.
     *
     * @param string $blockHash
     * @param string $periodCode
     * @param array $expectedRed
     * @param array $expectedBlue
     * @return bool
     */
    public function verifyWinningNumbers(
        string $blockHash,
        string $periodCode,
        array $expectedRed,
        array $expectedBlue
    ): bool {
        $config = config('nexus_plugin_dcb.game_rules');

        $generated = $this->generateWinningNumbers(
            $blockHash,
            $periodCode,
            $config['red_ball_count'],
            $config['red_ball_max'],
            $config['blue_ball_count'],
            $config['blue_ball_max']
        );

        sort($expectedRed);
        sort($expectedBlue);

        return $generated['red'] === $expectedRed 
            && $generated['blue'] === $expectedBlue;
    }

    /**
     * Get fallback block when Bitcoin API is unavailable.
     *
     * @return array{hash: string, height: int}
     */
    private function getFallbackBlock(): array
    {
        // Use current timestamp and random data as fallback
        $timestamp = now()->timestamp;
        $random = bin2hex(random_bytes(32));
        $hash = hash('sha256', $timestamp . $random);

        return [
            'hash' => $hash,
            'height' => 0, // Indicates fallback was used
        ];
    }
}
