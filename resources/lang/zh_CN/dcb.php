<?php

return [
    'name' => '双色球',
    'description' => '趣味双色球彩票插件',

    'labels' => [
        'red_balls' => '红球',
        'blue_balls' => '蓝球',
        'period_code' => '期号',
        'prize_pool' => '奖池',
        'winning_numbers' => '中奖号码',
        'my_tickets' => '我的彩票',
        'draw_history' => '开奖历史',
        'fairness_verify' => '公平性验证',
        'select_numbers' => '选择号码',
        'quick_pick' => '机选',
        'buy_ticket' => '购买彩票',
        'cost' => '花费',
        'magic_points' => '魔力值',
        'balance' => '余额',
        'ticket_count' => '注数',
        'win_level' => '中奖等级',
        'prize_amount' => '奖金',
        'purchase_time' => '购买时间',
        'draw_time' => '开奖时间',
        'block_hash' => '区块哈希',
        'block_height' => '区块高度',
    ],

    'status' => [
        'open' => '售票中',
        'closed' => '已截止',
        'drawn' => '已开奖',
        'unknown' => '未知',
    ],

    'win_level' => [
        'no_win' => '未中奖',
        'level_1' => '一等奖',
        'level_2' => '二等奖',
        'level_3' => '三等奖',
        'level_4' => '四等奖',
        'level_5' => '五等奖',
    ],

    'messages' => [
        'select_red_balls' => '请选择 :count 个红球 (1-:max)',
        'select_blue_balls' => '请选择 :count 个蓝球 (1-:max)',
        'purchase_success' => '购买成功！',
        'purchase_failed' => '购买失败：:reason',
        'insufficient_balance' => '魔力值不足',
        'period_closed' => '本期已截止售票',
        'max_tickets_reached' => '已达到单期购买上限',
        'invalid_numbers' => '号码选择无效',
        'draw_success' => '开奖成功',
        'draw_failed' => '开奖失败：:reason',
        'congratulations' => '恭喜中奖！',
        'no_current_period' => '当前没有开放的期号',
    ],

    'notification' => [
        'win_subject' => '【双色球】中奖通知',
        'win_body' => '恭喜您在第 :period 期中获得 :level 等奖，奖金 :amount 魔力值已发放到您的账户！',
    ],

    'buttons' => [
        'buy_now' => '立即购买',
        'clear_selection' => '清空选择',
        'quick_pick' => '机选',
        'view_my_tickets' => '查看我的彩票',
        'view_history' => '开奖历史',
        'verify_fairness' => '验证公平性',
        'verify' => '验证',
    ],

    'admin' => [
        'periods' => '期号管理',
        'tickets' => '注码管理',
        'config' => '配置管理',
        'manual_draw' => '手动开奖',
        'create_period' => '创建新期',
        'game_rules' => '游戏规则',
        'prize_allocation' => '奖金分配',
        'draw_schedule' => '开奖时间',
    ],

    'help' => [
        'how_to_play' => '如何玩',
        'how_to_play_text' => '从 1-:red_max 中选择 :red_count 个红球，从 1-:blue_max 中选择 :blue_count 个蓝球。每注花费 :price 魔力值。',
        'prize_rules' => '奖金规则',
        'fairness_intro' => '本系统采用比特币区块哈希作为随机种子，确保开奖结果公正透明，任何人都可以验证。',
        'how_to_verify' => '如何验证',
        'how_to_verify_text' => '输入期号，系统将显示该期使用的比特币区块哈希，并实时计算中奖号码。您可以对比实际开奖结果来验证公平性。',
    ],
];
