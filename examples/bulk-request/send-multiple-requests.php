<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$requests = [
    [
        'uri' => 'https://httpbin.org/get',
    ],
    [
        'method' => 'GET',
        'uri' => 'https://httpbin.org/get',
        'options' => [
            'query' => [
                'foo' => 'bar',
            ],
        ],
    ],
    [
        'method' => 'POST',
        'uri' => 'https://httpbin.org/post',
        'options' => [
            'headers' => [
                'User-Agent' => 'EasyHttp/1.0.0',
            ],
            'body' => [
                'foo' => 'bar',
            ]
        ],
    ],
    [
        'uri' => 'https://httpbin.org/post',
        'options' => [
            'body' => "Hello World",
        ],
    ]
];

foreach ((new \EasyHttp\Client())->bulk($requests) as $response) {
    if ($response->getStatusCode() == 200) {
        echo '<pre>' . $response->getBody() . '</pre>';
    } else {
        echo '<pre>Error: ' . $response->getError() . '</pre>';
    }
}