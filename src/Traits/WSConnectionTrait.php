<?php declare(strict_types=1);

namespace EasyHttp\Traits;

use EasyHttp\Contracts\CommonsContract;
use EasyHttp\Exceptions\BadOpcodeException;
use EasyHttp\Exceptions\ConnectionException;
use EasyHttp\Exceptions\WebSocketException;
use EasyHttp\WebSocketConfig;

/**
 * WSConnectionTrait class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
trait WSConnectionTrait
{

    /**
     * Allowed events
     *
     * @var array
     */
    private array $allowedEvents = [
        'open',
        'close',
        'error',
        'message',
        'meantime',
    ];

    /**
     * Registered events
     *
     * @var array
     */
    private array $registeredEvents = [];

    /**
     * @var bool
     */
    private bool $isConnected = false;

    /**
     * @var bool
     */
    private bool $isClosing = false;

    /**
     * Default headers
     *
     * @var array
     */
    private array $defaultHeaders = [
        'Connection' => 'Upgrade',
        'Upgrade' => 'WebSocket',
        'Sec-Websocket-Version' => '13',
    ];

    /**
     * Reconnect to the Web Socket server
     *
     * @return void
     * @throws \Exception
     */
    public function reconnect(): void
    {
        if ($this->isConnected) {
            $this->close();
        }

        $this->connect($this->socketUrl, $this->config);
    }

    /**
     * Tell the socket to close.
     *
     * @param integer $status https://github.com/Luka967/websocket-close-codes
     * @param string $message A closing message, max 125 bytes.
     * @return bool|null|string
     * @throws \Exception
     */
    public function close(int $status = 1000, string $message = 'ttfn'): bool|null|string
    {
        $statusBin = sprintf('%016b', $status);
        $statusStr = '';

        foreach (str_split($statusBin, 8) as $binstr) {
            $statusStr .= chr(bindec($binstr));
        }

        $this->send($statusStr . $message, CommonsContract::EVENT_TYPE_CLOSE);
        $this->closeStatus = $status;
        $this->isClosing = true;

        return $this->receive(); // Receiving a close frame will close the socket now.
    }

    /**
     * Sends message to opened socket connection client->server
     *
     * @param $payload
     * @param string $opcode
     */
    public function send($payload, string $opcode = CommonsContract::EVENT_TYPE_TEXT): void
    {
        if (!$this->isConnected) {
            throw new WebSocketException(
                "Can't send message. Connection is not established.",
                CommonsContract::CLIENT_CONNECTION_NOT_ESTABLISHED
            );
        }

        if (array_key_exists($opcode, self::$opcodes) === false) {
            throw new BadOpcodeException(
                sprintf("Bad opcode '%s'.  Try 'text' or 'binary'.", $opcode),
                CommonsContract::CLIENT_BAD_OPCODE
            );
        }

        $payloadLength = strlen($payload);
        $fragmentCursor = 0;

        while ($payloadLength > $fragmentCursor) {
            $subPayload = substr($payload, $fragmentCursor, $this->config->getFragmentSize());
            $fragmentCursor += $this->config->getFragmentSize();
            $final = $payloadLength <= $fragmentCursor;
            $this->sendFragment($final, $subPayload, $opcode, true);
            $opcode = 'continuation';
        }
    }

    /**
     * Receives message client<-server
     *
     * @return string|null
     * @throws ConnectionException
     */
    public function receive(): string|null
    {
        if (!$this->isConnected && $this->isClosing === false) {
            throw new WebSocketException(
                "Your unexpectedly disconnected from the server",
                CommonsContract::CLIENT_CONNECTION_NOT_ESTABLISHED
            );
        }

        $this->hugePayload = '';

        return $this->receiveFragment();
    }

    /**
     * @param string $socketUrl string that represents the URL of the Web Socket server. e.g. ws://localhost:1337 or wss://localhost:1337
     * @param ?WebSocketConfig $config The configuration for the Web Socket client
     * @throws ConnectionException
     */
    public function connect(string $socketUrl, ?WebSocketConfig $config = null): void
    {
        $this->config = $config ?? new WebSocketConfig();
        $this->socketUrl = $socketUrl;
        $urlParts = parse_url($this->socketUrl);

        $this->config->setScheme($urlParts['scheme']);
        $this->config->setHost($urlParts['host']);
        $this->config->setUser($urlParts);
        $this->config->setPassword($urlParts);
        $this->config->setPort($urlParts);

        $pathWithQuery = $this->getPathWithQuery($urlParts);
        $hostUri = $this->getHostUri($this->config);

        $context = $this->getStreamContext();
        if ($this->config->hasProxy()) {
            $this->socket = $this->proxy();
        } else {
            $this->socket = @stream_socket_client(
                $hostUri . ':' . $this->config->getPort(),
                $errno,
                $errstr,
                $this->config->getTimeout(),
                STREAM_CLIENT_CONNECT,
                $context
            );
        }

        if ($this->socket === false) {
            throw new ConnectionException(
                "Could not open socket to \"{$this->config->getHost()}:{$this->config->getPort()}\": $errstr ($errno).",
                CommonsContract::CLIENT_COULD_NOT_OPEN_SOCKET
            );
        }

        stream_set_timeout($this->socket, $this->config->getTimeout());

        $key = $this->generateKey();
        $headers = array_merge($this->defaultHeaders, [
            'Host' => $this->config->getHost() . ':' . $this->config->getPort(),
            'User-Agent' => 'Easy-Http/' . self::VERSION . ' (PHP/' . PHP_VERSION . ')',
            'Sec-WebSocket-Key' => $key,
        ]);

        if ($this->config->getUser() || $this->config->getPassword()) {
            $headers['authorization'] = 'Basic ' . base64_encode($this->config->getUser() . ':' . $this->config->getPassword()) . "\r\n";
        }

        if (!empty($this->config->getHeaders())) {
            $headers = array_merge($headers, $this->config->getHeaders());
        }

        $header = $this->getHeaders($pathWithQuery, $headers);

        $this->write($header);

        $this->validateResponse($this->config, $pathWithQuery, $key);
        $this->isConnected = true;
        $this->whileIsConnected();
    }

    /**
     * @return void
     */
    private function whileIsConnected(): void
    {
        $this->safeCall('open', $this);

        while ($this->isConnected() && $this->isClosing === false) {
            $this->safeCall('meantime', $this);

            if (is_string(($message = $this->receive()))) {
                $this->safeCall('message', $this, $message);
            }
        }

        $this->safeCall('close', $this, $this->closeStatus);
    }

    /**
     * Execute events with safety of exceptions
     *
     * @param string $type The type of event to execute
     * @param mixed ...$args
     * @return void
     */
    private function safeCall(string $type, ...$args): void
    {
        if (isset($this->registeredEvents[$type])) {

            if (is_callable($this->registeredEvents[$type])) {
                call_user_func_array($this->registeredEvents[$type], $args);
                return;
            }

            throw new WebSocketException(sprintf(
                "The event '%s' is not callable.", $type
            ), CommonsContract::CLIENT_EVENT_NOT_CALLABLE);
        }
    }

    /**
     * Register a event listener
     *
     * @param string $event
     * @param callable $callback
     *
     * @return void
     */
    public function on(string $event, callable $callback): void
    {
        if (!in_array($event, $this->allowedEvents)) {
            throw new \RuntimeException("Event {$event} not allowed");
        }

        $this->registeredEvents[$event] = $callback;
    }

}