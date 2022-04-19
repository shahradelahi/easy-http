<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

// Specifics and defines the test environment
$endpoint = 'https://scanner.tradingview.com/crypto/scan';
$post = [
    'symbols' => [
        'tickers' => [],
        'query' => [
            'types' => []
        ]
    ],
    'columns' => [
        "open",
        "high",
        "low",
        "volume",
        "close",
        "EMA|10",
        "EMA|50",
    ],
    'range' => []
];

// --------------------- ====== --------------------- //

// Initializes the request
$requests = [];
for ($i = 0; $i < 1000; $i += 100) {

    $post['range'][] = [
        'from' => $i,
        'to' => $i + 100
    ];

    $requests[] = [
        'method' => 'POST',
        'url' => $endpoint,
        'options' => [
            'body' => json_encode($post)
        ]
    ];

}

// --------------------- ====== --------------------- //

$merger = [];
$responses = (new \EasyHttp\Client())->bulk($requests); # Bulk request
foreach ($responses as $response) {
    $merger = array_merge($merger, json_decode($response->getBody(), true)); # Merge responses
}

echo '<pre>' . json_encode($merger, JSON_PRETTY_PRINT) . '</pre>'; # Prints the result