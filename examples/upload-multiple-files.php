<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$result = (new \EasyHttp\Client())->upload('https://httpbin.org/post', [
	'photo1' => getcwd() . '/../docs/result-of-breakdown-example.png',
	'photo2' => getcwd() . '/../docs/download.png',
]);

echo '<pre>' . \EasyHttp\Util\Utils::prettyJson($result->getBody()) . '</pre>';
