<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$DirectLink = 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png';


$client = new \EasyHttp\Client();
$client->setTempPath(__DIR__ . '/tmp');
$Result = $client->download($DirectLink);
echo '<pre>' . json_encode($Result->downloads, JSON_PRETTY_PRINT) . '</pre>';


if ($Result->save(__DIR__ . '/uploads/google.png')) {
	echo 'File saved successfully';
} else {
	echo 'File not saved';
}