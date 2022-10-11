<?php declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$DirectLink = 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png';

$Result = EasyHttp\download($DirectLink);
echo '<pre>' . json_encode($Result->chunks, JSON_PRETTY_PRINT) . '</pre>';


if ($Result->save(__DIR__ . '/uploads/google.png')) {
    echo 'File saved successfully';
} else {
    echo 'File not saved';
}