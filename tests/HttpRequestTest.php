<?php declare(strict_types=1);

namespace EasyHttp\Tests;

use EasyHttp\HttpClient;

class HttpRequestTest extends \PHPUnit\Framework\TestCase
{

    public function testPostRequest(): void
    {
        $response = (new HttpClient())->post('https://httpbin.org/post', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => [
                'name' => 'John Doe',
                'age' => '25'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(
            json_decode('{"name":"John Doe","age":"25"}', true),
            json_decode($response->getBody(), true)['json']
        );
    }

    public function testGetRequest(): void
    {
        $response = (new HttpClient())->get('https://httpbin.org/get', [
            'headers' => [
                'User-Agent' => 'EasyHttp/v1.1.2 (PHP: ' . PHP_VERSION . ')',
                'Accept' => 'application/json'
            ],
            'query' => [
                'customer' => 'John Doe'
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('EasyHttp/v1.1.2 (PHP: ' . PHP_VERSION . ')', $body['headers']['User-Agent']);
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('John Doe', $body['args']['customer']);
    }

}