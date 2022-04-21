# Send Multiple Requests at once

The source code of this documentation is available
at [`send-multiple-requests.php`](../examples/send-multiple-requests.php).

### Getting Started

In order to send multiple requests at once, you need to create a `Client` instance.

```php
$client = new \EasyHttp\Client();
```

We have two ways to creating requests and the first one is `Request class`:

```php
$requests = [
    new \EasyHttp\Request('GET', 'https://httpbin.org/get'),
    new \EasyHttp\Request('GET', 'https://httpbin.org/get'),
    new \EasyHttp\Request('GET', 'https://httpbin.org/get'),
];
```

or simply just use the `array syntax` to create your requests:

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
$client = new \EasyHttp\Client();

$requests = [
    [
        'method' => 'GET',
        'url' => 'https://httpbin.org/get',
        'options' => [
            'timeout' => 10,
        ],
    ],
    [
        'method' => 'GET',
        'url' => 'https://httpbin.org/get',
        'options' => [
            'queries' => [
                'foo' => 'bar',
            ],
        ],
    ],
    [
        'method' => 'GET',
        'url' => 'https://httpbin.org/get',
        'options' => [
            'headers' => [
                'foo' => 'bar',
            ],
        ],
    ],
];

$responses = $client->bulk($requests);
foreach ($responses as $response) {
    echo $response->getBody(); // outputs the response body
}
```

<details>
<summary>Click here to see the source code</summary>

```php
$responses = $client->bulk($requests);
foreach ($responses as $response) {
    echo '<pre>' . $response->getBody() . '</pre>';
}
```

</details>