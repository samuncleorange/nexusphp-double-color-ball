<?php

return [
    'name' => 'Double Color Ball',
    'description' => 'Fun lottery plugin for NexusPHP',

    'labels' => [
        'red_balls' => 'Red Balls',
        'blue_balls' => 'Blue Balls',
        'period_code' => 'Period',
        'prize_pool' => 'Prize Pool',
        'winning_numbers' => 'Winning Numbers',
        'my_tickets' => 'My Tickets',
        'draw_history' => 'Draw History',
        'fairness_verify' => 'Fairness Verification',
        'select_numbers' => 'Select Numbers',
        'quick_pick' => 'Quick Pick',
        'buy_ticket' => 'Buy Ticket',
        'cost' => 'Cost',
        'magic_points' => 'Magic Points',
        'balance' => 'Balance',
        'ticket_count' => 'Tickets',
        'win_level' => 'Win Level',
        'prize_amount' => 'Prize',
        'purchase_time' => 'Purchase Time',
        'draw_time' => 'Draw Time',
        'block_hash' => 'Block Hash',
        'block_height' => 'Block Height',
    ],

    'status' => [
        'open' => 'Open',
        'closed' => 'Closed',
        'drawn' => 'Drawn',
        'unknown' => 'Unknown',
    ],

    'win_level' => [
        'no_win' => 'No Win',
        'level_1' => '1st Prize',
        'level_2' => '2nd Prize',
        'level_3' => '3rd Prize',
        'level_4' => '4th Prize',
        'level_5' => '5th Prize',
    ],

    'messages' => [
        'select_red_balls' => 'Please select :count red balls (1-:max)',
        'select_blue_balls' => 'Please select :count blue balls (1-:max)',
        'purchase_success' => 'Purchase successful!',
        'purchase_failed' => 'Purchase failed: :reason',
        'insufficient_balance' => 'Insufficient magic points',
        'period_closed' => 'Period is closed',
        'max_tickets_reached' => 'Maximum tickets per period reached',
        'invalid_numbers' => 'Invalid number selection',
        'draw_success' => 'Draw successful',
        'draw_failed' => 'Draw failed: :reason',
        'congratulations' => 'Congratulations!',
        'no_current_period' => 'No current period available',
    ],

    'notification' => [
        'win_subject' => '[Double Color Ball] Win Notification',
        'win_body' => 'Congratulations! You won :level in period :period. Prize of :amount magic points has been credited to your account!',
    ],

    'buttons' => [
        'buy_now' => 'Buy Now',
        'clear_selection' => 'Clear',
        'quick_pick' => 'Quick Pick',
        'view_my_tickets' => 'My Tickets',
        'view_history' => 'History',
        'verify_fairness' => 'Verify Fairness',
        'verify' => 'Verify',
    ],

    'admin' => [
        'periods' => 'Periods',
        'tickets' => 'Tickets',
        'config' => 'Configuration',
        'manual_draw' => 'Manual Draw',
        'create_period' => 'Create Period',
        'game_rules' => 'Game Rules',
        'prize_allocation' => 'Prize Allocation',
        'draw_schedule' => 'Draw Schedule',
    ],

    'help' => [
        'how_to_play' => 'How to Play',
        'how_to_play_text' => 'Select :red_count red balls from 1-:red_max and :blue_count blue balls from 1-:blue_max. Each ticket costs :price magic points.',
        'prize_rules' => 'Prize Rules',
        'fairness_intro' => 'This system uses Bitcoin block hash as random seed to ensure fair and transparent draw results. Anyone can verify the results.',
        'how_to_verify' => 'How to Verify',
        'how_to_verify_text' => 'Enter the period code, and the system will display the Bitcoin block hash used for that period and calculate the winning numbers in real-time. You can compare with actual results to verify fairness.',
    ],
];
