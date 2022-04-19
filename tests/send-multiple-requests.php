<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$requests = [];

// A Post request with Json Body and Custom Headers
$requests[] = [
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
];

// A Simple Post request (Method type by default will be POST)
$requests[] = [
    'uri' => 'https://httpbin.org/post',
    'options' => [
        'body' => "Hello World",
    ],
];

// A Get request with query params
$requests[] = [
    'method' => 'GET',
    'uri' => 'https://httpbin.org/get',
    'options' => [
        'queries' => [
            'foo' => 'bar',
        ],
    ],
];

// A Simple Get request (Method type by default will be GET)
$requests[] = [
    'uri' => 'https://httpbin.org/get',
];

// Pass the requests to the bulk request function
$responses = (new \EasyHttp\Client())->bulk($requests);

// Print the responses
foreach ($responses as $response) {
    echo $response->getBody() . '<br><br>';
}