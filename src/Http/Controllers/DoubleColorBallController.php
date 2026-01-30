<?php

namespace NexusPlugin\DoubleColorBall\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NexusPlugin\DoubleColorBall\Repositories\PeriodRepository;
use NexusPlugin\DoubleColorBall\Repositories\TicketRepository;
use NexusPlugin\DoubleColorBall\Services\ProvablyFairService;

/**
 * Double Color Ball Controller
 * 
 * Handles frontend lottery operations.
 */
class DoubleColorBallController extends Controller
{
    protected PeriodRepository $periodRepository;
    protected TicketRepository $ticketRepository;
    protected ProvablyFairService $provablyFairService;

    public function __construct()
    {
        $this->periodRepository = new PeriodRepository();
        $this->ticketRepository = new TicketRepository();
        $this->provablyFairService = new ProvablyFairService();
    }

    /**
     * Display the main lottery page.
     */
    public function index()
    {
        $user = Auth::user();
        $currentPeriod = $this->periodRepository->getCurrentOpenPeriod();
        $config = config('nexus_plugin_dcb');

        // Get user's tickets for current period
        $userTickets = [];
        if ($currentPeriod) {
            $userTickets = $this->ticketRepository->getUserTickets($user->id, $currentPeriod->id);
        }

        // Get recent draw history
        $recentPeriods = $this->periodRepository->getRecentPeriods(5);

        return view('dcb::index', [
            'user' => $user,
            'currentPeriod' => $currentPeriod,
            'userTickets' => $userTickets,
            'recentPeriods' => $recentPeriods,
            'config' => $config,
        ]);
    }

    /**
     * Purchase a ticket.
     */
    public function buy(Request $request)
    {
        $user = Auth::user();

        try {
            $validated = $request->validate([
                'period_id' => 'required|integer|exists:nexus_plugin_dcb_periods,id',
                'red_balls' => 'required|array',
                'red_balls.*' => 'required|integer|min:1',
                'blue_balls' => 'required|array',
                'blue_balls.*' => 'required|integer|min:1',
            ]);

            $ticket = $this->ticketRepository->purchaseTicket(
                $user->id,
                $validated['period_id'],
                $validated['red_balls'],
                $validated['blue_balls']
            );

            return response()->json([
                'success' => true,
                'message' => nexus_trans('dcb::dcb.messages.purchase_success'),
                'ticket' => $ticket,
            ]);
        } catch (\LogicException $e) {
            return response()->json([
                'success' => false,
                'message' => nexus_trans('dcb::dcb.messages.purchase_failed', ['reason' => $e->getMessage()]),
            ], 400);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => nexus_trans('dcb::dcb.messages.invalid_numbers'),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => nexus_trans('dcb::dcb.messages.purchase_failed', ['reason' => 'System error']),
            ], 500);
        }
    }

    /**
     * Display user's tickets.
     */
    public function myTickets()
    {
        $user = Auth::user();
        $tickets = $this->ticketRepository->getAllUserTickets($user->id, 20);
        $totalWinnings = $this->ticketRepository->getUserTotalWinnings($user->id);
        $totalSpending = $this->ticketRepository->getUserTotalSpending($user->id);

        return view('dcb::my-tickets', [
            'user' => $user,
            'tickets' => $tickets,
            'totalWinnings' => $totalWinnings,
            'totalSpending' => $totalSpending,
        ]);
    }

    /**
     * Display draw history.
     */
    public function history()
    {
        $periods = $this->periodRepository->getDrawnPeriods(20);

        return view('dcb::history', [
            'periods' => $periods,
        ]);
    }

    /**
     * Display fairness verification page.
     */
    public function verify()
    {
        return view('dcb::verify');
    }

    /**
     * Perform fairness verification.
     */
    public function doVerify(Request $request)
    {
        try {
            $validated = $request->validate([
                'period_code' => 'required|string',
            ]);

            $period = $this->periodRepository->getByCode($validated['period_code']);

            if (!$period || !$period->isDrawn()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Period not found or not drawn yet.',
                ], 404);
            }

            // Regenerate winning numbers using the same algorithm
            $config = config('nexus_plugin_dcb.game_rules');
            $generatedNumbers = $this->provablyFairService->generateWinningNumbers(
                $period->block_hash,
                $period->period_code,
                $config['red_ball_count'],
                $config['red_ball_max'],
                $config['blue_ball_count'],
                $config['blue_ball_max']
            );

            // Verify
            $isValid = $this->provablyFairService->verifyWinningNumbers(
                $period->block_hash,
                $period->period_code,
                $period->red_balls,
                $period->blue_balls
            );

            return response()->json([
                'success' => true,
                'period' => [
                    'code' => $period->period_code,
                    'block_hash' => $period->block_hash,
                    'block_height' => $period->block_height,
                    'red_balls' => $period->red_balls,
                    'blue_balls' => $period->blue_balls,
                ],
                'generated' => $generatedNumbers,
                'is_valid' => $isValid,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
