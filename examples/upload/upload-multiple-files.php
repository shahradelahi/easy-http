<?php declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$result = EasyHttp\upload('https://httpbin.org/post', [
    'photo1' => getcwd() . '/../docs/uploads/result-of-breakdown-example.png',
    'photo2' => getcwd() . '/../docs/uploads/download.png',
]);

echo '<pre>' . \EasyHttp\Utils\Toolkit::prettyJson($result->getBody()) . '</pre>';
