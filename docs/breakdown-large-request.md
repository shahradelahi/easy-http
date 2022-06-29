# Breakdown of Large Request

On this page, we will show you how to break down a large request into smaller requests and make the process of sending the requests easier and faster.

<br/>

#### Table of Contents
- [Initialize Environments](#initialize-environments)
- [Normal CURL](#normal-curl)
    - [Send the request](#normal-curl-send-the-request)
- [Bulk Request](#bulk-request)
    - [Creating the Requests](#bulk-request-create-requests)
    - [Send Requests](#bulk-request-send-requests)
- [Result](#result)

<br/>

### Getting Started
The following is a breakdown of the request we will get data from `API` of `TradingView` and the source code of this example is available at [here](../examples/bulk-request/breakdown-large-request.php).

<br/>

#### Initialize Environments
The `$postData` variable is the data that we will send to the API and you can customize it to your needs.
```php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
$client = new \EasyHttp\Client();
$endPoint = 'https://scanner.tradingview.com/america/scan';
$postData = [
    'filter' => [
        [
            'left' => "exchange",
            'operation' => "in_range",
            'right' => [
                'AMEX',
                'NASDAQ',
                'NYSE'
            ],
        ],
        [
            'left' => "is_primary",
            'operation' => "equal",
            'right' => true,
        ],
        [
            'left' => "change",
            'operation' => "nempty",
        ]
    ],
    'options' => [
        'lang' => "en",
        'active_symbols_only' => true,
    ],
    'markets' => [
        "america"
    ],
    'columns' => [
        "logoid",
        "name",
        "close",
        // And so on...
    ],
    'sort' => [
        'sortBy' => "change",
        'sortOrder' => "desc"
    ]
];
```

<br/>

### Normal CURL
On this part we will show you how to send the request using the normal CURL.

#### Normal CURL: Send the request
```php
$startTime = microtime(true);
$response = $client->post($endPoint, [
    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],
    'body' => array_merge($postData, [
        'range' => [
            0,
            2000
        ]
    ]),
]);
echo '<pre>' . "Normal CURL - Total time: " . (microtime(true) - $startTime) . " - Memory: $Memory" . '</pre>';
```

<br/>

### Bulk Request
On this part we will show you how to send the request using the bulk request.

#### Bulk Request: Create Requests
```php
$requests = [];
for ($i = 0; $i < 2000; $i += 500) {
    $requests[] = [
        'uri' => $endPoint,
        'options' => [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => array_merge($postData, [
                'range' => [
                    $i,
                    $i + 500
                ]
            ]),
        ]
    ];
}
```

#### Bulk Request: Send Requests

```php

$start = microtime(true);
$responses = $client->bulk($requests);
foreach ($responses as $response) {
    $Data = json_decode($response->getBody(), true);
}
$Memory = \EasyHttp\Utils\Toolkit::bytesToHuman(memory_get_usage());
echo '<pre>' . "Bulk Request - Total time: " . (microtime(true) - $start) . " - Memory: $Memory" . '</pre>';
```

<br/>

#### Result
The result of this example is showing us that the bulk request is much faster than the normal request.
```txt
Normal CURL - Total time: 6.0051791667938 - Memory: 10.21 MiB
Bulk Request - Total time: 2.0174889564514 - Memory: 7.91 MiB
```