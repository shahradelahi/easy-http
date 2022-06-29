<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use EasyHttp\Utils\WSConfig;
use EasyHttp\WebSocket;

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * @param array $ids
 * @param callable $callback
 */
function startTicker(array $ids, callable $callback): void
{
	try {

		$close_time = time() + 10;
		$ClientConfig = (new WSConfig())->setFragmentSize(8096)->setTimeout(15);
		$WebSocketClient = new WebSocket('wss://stream.coinmarketcap.com/price/latest', $ClientConfig);

		$WebSocketClient->send(json_encode([
			'method' => 'subscribe',
			'id' => 'price',
			'data' => [
				'cryptoIds' => $ids,
				'index' => 'detail'
			]
		]));

		while ($close_time > time()) {
			if (($message = $WebSocketClient->receive()) != "") {
				$json_response = json_decode($message, true);

				if ($json_response['id'] == "price") {
					$callback($json_response);
				}
			}
		}

	} catch (Exception $e) {
		echo "<b>Error</b>: " . $e->getMessage();
	}
}

echo "<pre>Start Tome: " . date('Y-m-d H:i:s') . "</pre><br/>";

$responses = [];
startTicker([1, 1027, 825, 3408, 1839, 4687, 52, 2010, 5426], function ($data) use (&$responses) {
	$responses[] = $data;
});

echo "<pre>" . json_encode($responses, JSON_PRETTY_PRINT) . "</pre><br/>";
echo "<pre>End Time: " . date('Y-m-d H:i:s') . "</pre>";