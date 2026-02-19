<?php
return [
    'telegram_token' => 'YOUR_BOT_TOKEN',
    'chat_id'        => 'YOUR_CHAT_ID',
    'history_days'   => 7,
    'thresholds'     => [
        'USD' => ['min' => 12400, 'max' => 12800, 'percent_change' => 0.5]
    ],
    'data_file'      => __DIR__ . '/data/history.json'
];
