<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// =============================== ***** =============================== //

echo <<<HTML
<p>
    <h1>Breakdown Large Request</h1>
    On this example we will require 2,000 stocks from the TradingView API, and then we will display the time and memory usage of the script.<br>
    <br><b>Note:</b> If you saw the "total time" does not make sense, it is a fault of the server or your network.<br>
    <b>Disclaimer:</b> This is just a code for purpose of education. It's illegal and should not be used in production.<br><br>
</p>
HTML;

// --------------------- ====== --------------------- //

$client = new \EasyHttp\Client();
$endpoint = 'https://scanner.tradingview.com/america/scan';
$post = [
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
		"change|1",
		"change|5",
		"change|15",
		"change|60",
		"change|240",
		"change",
		"change|1W",
		"change|1M",
		"Perf.3M",
		"Perf.6M",
		"Perf.YTD",
		"Perf.Y",
		"Volatility.D",
		"description",
		"type",
		"subtype",
		"update_mode",
		"currency",
		"fundamental_currency_code",
		"Recommend.All",
		"RSI",
		"RSI[1]",
		"Stoch.K",
		"Stoch.D",
		"Stoch.K[1]",
		"Stoch.D[1]",
		"CCI20",
		"CCI20[1]",
		"AO",
		"AO[1]",
		"AO[2]",
		"Mom",
		"Mom[1]",
		"MACD.macd",
		"MACD.signal",
		"Rec.Stoch.RSI",
		"Stoch.RSI.K",
		"Rec.WR",
		"W.R",
		"Rec.BBPower",
		"BBPower",
		"Rec.UO",
		"UO",
		"EMA10",
		"close",
		"SMA10",
		"EMA20",
		"SMA20",
		"EMA30",
		"SMA30",
		"EMA50",
		"SMA50",
		"EMA100",
		"SMA100",
		"EMA200",
		"SMA200",
		"Rec.Ichimoku",
		"Ichimoku.BLine",
		"Rec.VWMA",
		"VWMA",
		"Rec.HullMA9",
		"HullMA9"
	],
	'sort' => [
		'sortBy' => "change",
		'sortOrder' => "desc"
	]
];

$start = microtime(true);
$Response = $client->post($endpoint, [
	'headers' => [
		'Content-Type' => 'application/json',
		'Accept' => 'application/json',
	],
	'body' => array_merge($post, [
		'range' => [
			0,
			2000
		]
	]),
]);
$Data = json_decode($Response->getBody(), true);
$Memory = \EasyHttp\Utils\Toolkit::bytesToHuman(memory_get_usage());
echo '<pre>' . "Normal CURL - Total time: " . (microtime(true) - $start) . " - Memory: $Memory" . '</pre>';
echo '<br/>';

// --------------------- ====== --------------------- //

// Initializes the request
$requests = [];
for ($i = 0; $i < 2000; $i += 500) {
	$requests[] = [
		'method' => 'POST',
		'uri' => $endpoint,
		'options' => [
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			],
			'body' => array_merge($post, [
				'range' => [
					$i,
					$i + 500
				]
			]),
		]
	];
}

// --------------------- ====== --------------------- //

$start = microtime(true);
$responses = $client->bulk($requests);
foreach ($responses as $response) {
	$Data = json_decode($response->getBody(), true);
}
$Memory = \EasyHttp\Utils\Toolkit::bytesToHuman(memory_get_usage());
echo '<pre>' . "Bulk Request - Total time: " . (microtime(true) - $start) . " - Memory: $Memory" . '</pre>';