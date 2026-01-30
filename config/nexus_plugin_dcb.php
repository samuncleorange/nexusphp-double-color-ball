<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Game Rules Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the lottery game rules including ball counts and ranges.
    |
    */
    'game_rules' => [
        'red_ball_count' => env('DCB_RED_BALL_COUNT', 6),      // Number of red balls to select
        'red_ball_max'   => env('DCB_RED_BALL_MAX', 33),       // Maximum red ball number
        'blue_ball_count'=> env('DCB_BLUE_BALL_COUNT', 1),     // Number of blue balls to select
        'blue_ball_max'  => env('DCB_BLUE_BALL_MAX', 16),      // Maximum blue ball number
    ],

    /*
    |--------------------------------------------------------------------------
    | Ticket Price Configuration
    |--------------------------------------------------------------------------
    |
    | Set the price per ticket in magic points (seed bonus).
    |
    */
    'price_per_ticket' => env('DCB_PRICE_PER_TICKET', 500),

    /*
    |--------------------------------------------------------------------------
    | Purchase Limits
    |--------------------------------------------------------------------------
    |
    | Maximum number of tickets a user can purchase per period.
    |
    */
    'max_tickets_per_user' => env('DCB_MAX_TICKETS_PER_USER', 100),

    /*
    |--------------------------------------------------------------------------
    | Prize Allocation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure prize allocation for each winning level.
    | 
    | Type 'ratio': Prize is a percentage of the prize pool
    |   - 'value': Percentage (0.0 - 1.0)
    |   - 'min': Minimum guaranteed prize
    |
    | Type 'fixed': Fixed prize amount
    |   - 'value': Fixed amount in magic points
    |
    */
    'prize_allocation' => [
        1 => [
            'type' => 'ratio',
            'value' => 0.70,        // 70% of prize pool
            'min' => 100000,        // Minimum 100,000 magic points
        ],
        2 => [
            'type' => 'ratio',
            'value' => 0.20,        // 20% of prize pool
            'min' => 10000,         // Minimum 10,000 magic points
        ],
        3 => [
            'type' => 'fixed',
            'value' => 3000,        // Fixed 3,000 magic points
        ],
        4 => [
            'type' => 'fixed',
            'value' => 200,         // Fixed 200 magic points
        ],
        5 => [
            'type' => 'fixed',
            'value' => 50,          // Fixed 50 magic points
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Draw Schedule Configuration
    |--------------------------------------------------------------------------
    |
    | Configure when the lottery draw should occur.
    |
    */
    'draw_schedule' => [
        'day' => 'sunday',              // Day of week: monday, tuesday, etc.
        'time' => '21:00',              // Time in HH:MM format (24-hour)
        'timezone' => 'Asia/Shanghai',  // Timezone
    ],

    /*
    |--------------------------------------------------------------------------
    | Bitcoin Block Hash API
    |--------------------------------------------------------------------------
    |
    | API endpoint to fetch Bitcoin block hash for provably fair algorithm.
    |
    */
    'bitcoin_api' => [
        'enabled' => env('DCB_BITCOIN_API_ENABLED', true),
        'endpoint' => env('DCB_BITCOIN_API_ENDPOINT', 'https://blockchain.info/latestblock'),
        'timeout' => 10, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure how winners are notified.
    |
    */
    'notifications' => [
        'send_pm' => true,              // Send private message to winners
        'pm_subject' => 'dcb.notification.win_subject',  // Translation key
        'pm_body' => 'dcb.notification.win_body',        // Translation key
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Theme Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the "Piggo Children" style UI theme.
    |
    */
    'ui_theme' => [
        'primary_color' => '#FF6B9D',       // Macaron pink
        'secondary_color' => '#4ECDC4',     // Sky blue
        'accent_color' => '#FFE66D',        // Cream yellow
        'enable_animations' => true,        // Enable cute animations
    ],
];
