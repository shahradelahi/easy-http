<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$client = new \EasyHttp\Client();

// Multiple files
$result = $client->upload('https://example.com/upload.php', [
    __DIR__ . '/file1.txt',
    __DIR__ . '/file2.txt',
]);

// Single file
$result = $client->upload(
    'https://example.com/upload.php',
    __DIR__ . '/file1.txt',
);

// With options
$result = $client->upload(
    'https://example.com/upload.php',
    __DIR__ . '/file1.txt',
    [
        'header' => [
            'X-Requested-With' => 'XMLHttpRequest',
            'User-Agent' => 'My-App',
        ],
    ]
);

if ($result->success) {
    echo 'Uploaded successfully';
} else {
    echo 'Error: ' . $result->response->getStatusCode();
}