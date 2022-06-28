<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

$Response = (new \EasyHttp\Client())->request('GET', 'https://httpbin.org/get');
echo '<pre>' . $Response->getBody() . '</pre>';

// --------------------- ====== --------------------- //

$Response = (new \EasyHttp\Client())->post('https://httpbin.org/post', [
	'headers' => [
		'Content-Type' => 'application/json'
	],
	'body' => [
		'name' => 'John Doe',
		'age' => '25'
	]
]);
echo '<pre>' . $Response->getBody() . '</pre>';

// --------------------- ====== --------------------- //

$Response = (new \EasyHttp\Client())->get('https://httpbin.org/get', [
	'headers' => [
		'Accept' => 'application/json',
		'User-Agent' => 'EasyHttp/1.0.0'
	]
]);
echo '<pre>' . $Response->getBody() . '</pre>';

// --------------------- ====== --------------------- //

$Response = (new \EasyHttp\Client())->get('https://httpbin.org/get', [
	'query' => [
		'name' => 'John Doe',
		'age' => '25'
	]
]);
echo '<pre>' . $Response->getBody() . '</pre>';