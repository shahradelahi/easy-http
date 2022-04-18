> **Help wanted:** If you can improve this library, please do so.
> ***Pull requests are welcome.***

# Easy Http
Easy Http is a lightweight HTTP client that is easy to use and integrates with your existing PHP application.

* Simple interface for building query strings, headers, and body.
* Supports all HTTP methods, and supports streaming of large files.
* **No dependency**, no need to install any third-party libraries.
* Supports multiple/bulk requests.

#### Installation

```sh
composer require shahradelahi/easy-http
```

<details>
 <summary>Click for help with installation</summary>

## Install Composer

If the above step didn't work, install composer and try again.

#### Debian / Ubuntu

```
sudo apt-get install curl php-curl
curl -s https://getcomposer.org/installer | php
php composer.phar install
```

Composer not found? Use this command instead:

```
php composer.phar require "shahradelahi/easy-http"
```

#### Windows:

[Download installer for Windows](https://github.com/jaggedsoft/php-binance-api/#installing-on-windows)

</details>

#### Getting started
```php
$client = new \EasyHttp\Client();
$response = $client->get('https://httpbin.org/get');

echo $response->getStatusCode(); // 200
echo $response->getHeaderLine('content-type'); // 'application/json'
echo $response->getBody(); // {"args":{},"headers":{},"origin":"**", ...}
```

### Documentation
We've created some sample of usage in below and if you have questions or want a new feature, please feel free to open [an issue](https://github.com/shahradelahi/easy-http/issues/new).

* [Send simple request](/tests/send-simple-request.php)
* [Slice a Large Data to pieces](/tests/slice-large-request.php)
* [Send multiple requests at once](/tests/send-multiple-requests.php)

### License
```
MIT License

Copyright (c) 2021 Shahrad Elahi

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```
