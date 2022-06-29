<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$your_token = '123456789:AAFniMNknE2Ba3rAqaWc9IU5Etq_HHou3rU';
$chat_id = '259760855';

$response = (new \EasyHttp\Client())->post('https://api.telegram.org/bot' . $your_token . '/sendPhoto', [
	'query' => [
		'chat_id' => $chat_id,
		'caption' => 'Привет!',
	],
	'multipart' => \EasyHttp\FormData::create([
		'photo' => getcwd() . '/../docs/uploads/download.png'
	])
]);


echo '<pre>' . \EasyHttp\Utils\Toolkit::prettyJson($response->getBody()) . '</pre>';