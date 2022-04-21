<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$DirectLink = 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png';

$client = new \EasyHttp\Client();

if (!file_exists(__DIR__ . '/tmp')) mkdir(__DIR__ . '/tmp');
$client->setTempPath(__DIR__ . '/tmp');

$Result = $client->download($DirectLink);

echo '<pre>' . json_encode($Result->downloads, JSON_PRETTY_PRINT) . '</pre>';

if (!file_exists(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads');

if ($Result->save(__DIR__ . '/uploads/google.png')) {
    echo 'File saved successfully';
} else {
    echo 'File not saved';
}