```php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
$EZClient = new \EasyHttp\Client();
```


```php
$Response = $EZClient->post('https://httpbin.org/post', [
	'headers' => [
		'Content-Type' => 'application/json'
	],
	'body' => [
		'name' => 'John Doe',
		'age' => '25'
	]
]);

echo '<pre>' . $Response->getBody() . '</pre>';
```


```php
$Response = $EZClient->get('https://httpbin.org/get', [
	'headers' => [
		'Accept' => 'application/json',
		'User-Agent' => 'EasyHttp/1.0.0'
	]
]);

echo '<pre>' . $Response->getBody() . '</pre>';
```

```php
$Response = $EZClient->get('https://httpbin.org/get', [
	'query' => [
		'name' => 'John Doe',
		'age' => '25'
	]
]);

echo '<pre>' . $Response->getBody() . '</pre>';
```