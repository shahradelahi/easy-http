<?php
require_once __DIR__ . '/../vendor/autoload.php';

// =============================== ***** =============================== //

$responses = (new \EasyHttp\Client())->bulk([
    [
        'method' => 'GET',
        'url' => 'https://httpbin.org/get',
        'options' => [
            'headers' => [
                'User-Agent' => 'EasyHttp/1.0.0',
            ],
        ],
    ],
    [
        'method' => 'GET',
        'url' => 'https://httpbin.org/get',
    ],
    [
        'url' => 'https://httpbin.org/get',
    ],
]);

// =============================== ***** =============================== //

foreach ($responses as $response) {
    echo $response->getBody() . '<br>';
}