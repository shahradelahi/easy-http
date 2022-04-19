<?php

namespace EasyHttp\Model;

/**
 * Proxy Server
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class ProxyServer
{

    /**
     * Proxy Server IP Address - IP or Domain
     *
     * @var ?string
     */
    public ?string $ip = null;

    /**
     * Proxy Server Port - 1-65535
     *
     * @var ?int
     */
    public ?int $port = null;

    /**
     * Proxy Server Username
     *
     * @var ?string
     */
    public ?string $username = null;

    /**
     * Proxy Server Password
     *
     * @var ?string
     */
    public ?string $password = null;

    /**
     * Proxy Server Type
     *
     * @var ?int [CURLPROXY_SOCKS5|CURLPROXY_SOCKS4|CURLPROXY_HTTP]
     */
    public ?int $type = null;

    /**
     * Setup Proxy Server
     *
     * @param array $proxy ["host", "port", "user", "pass"]
     * @return ProxyServer
     */
    public function setProxy(array $proxy): self
    {
        $this->ip = $proxy['host'];
        $this->port = $proxy['port'];
        $this->username = $proxy['user'];
        $this->password = $proxy['pass'];

        return $this;
    }

    /**
     * Set Proxy Server Type
     *
     * @param int $type [CURLPROXY_SOCKS5|CURLPROXY_SOCKS4|CURLPROXY_HTTP]
     * @return ProxyServer
     */
    public function setType(int $type): self
    {
        if (!in_array($type, [CURLPROXY_SOCKS5, CURLPROXY_SOCKS4, CURLPROXY_HTTP])) {
            throw new \InvalidArgumentException('Invalid Proxy Type');
        }
        $this->type = $type;

        return $this;
    }

    /**
     * Get Proxy Server
     *
     * @return ?string
     */
    public function getProxy(): ?string
    {
        return !empty($this->host) && !empty($this->port) ? "$this->host:$this->port" : null;
    }

    /**
     * Get auth data
     *
     * @return ?string
     */
    public function getAuth(): ?string
    {
        return !empty($this->username) && !empty($this->password) ? "$this->username:$this->password" : null;
    }

}