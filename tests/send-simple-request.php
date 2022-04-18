<?php
require_once __DIR__ . '/../vendor/autoload.php';

// =============================== ***** =============================== //

$Response = (new \EasyHttp\Client())->request('GET', 'https://httpbin.org/get');
echo $Response->body;

// --------------------- ====== --------------------- //

$Response = (new \EasyHttp\Client())->post('https://httpbin.org/post', [
    'body' => [
        'name' => 'John Doe',
        'age' => '25'
    ],
    'headers' => [
        'Content-Type' => 'application/json'
    ]
]);
echo $Response->body;

// --------------------- ====== --------------------- //

$Response = (new \EasyHttp\Client())->get('https://httpbin.org/get', [
    'headers' => [
        'Accept' => 'application/json',
        'User-Agent' => 'EasyHttp/1.0.0'
    ]
]);
echo $Response->body;

// --------------------- ====== --------------------- //

$Response = (new \EasyHttp\Client())->get('https://httpbin.org/get', [
    'queries' => [
        'name' => 'John Doe',
        'age' => '25'
    ]
]);
echo $Response->body;