# Send Multiple Requests at once

The source code of this documentation is available
at [`send-multiple-requests.php`](../examples/bulk-request/send-multiple-requests.php).

### Getting Started

In order to send multiple requests at once, you need to create a `Client` instance.

```php
$client = new \EasyHttp\Client();
```

Create your requests:

```php
$requests = [
    [
        'method' => 'GET',
        'url' => 'https://httpbin.org/get',
    ],
    [
        'method' => 'GET',
        'url' => 'https://httpbin.org/get',
    ],
    [
        'method' => 'GET',
        'url' => 'https://httpbin.org/get',
    ],
];
```

After you have created the requests, you can send them all at once by using the `bulk` method.

```php
$responses = $client->bulk($requests);
foreach ($responses as $response) {
    echo $response->getBody() . PHP_EOL; // outputs the response body
}
```