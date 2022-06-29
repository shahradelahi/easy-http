> **Help wanted:** If you can improve this library, please do so.
> ***Pull requests are welcome.***

# Easy Http

[![Build Status](https://scrutinizer-ci.com/g/shahradelahi/easy-http/badges/build.png?b=master)](https://scrutinizer-ci.com/g/shahradelahi/easy-http/build-status/master)
[![Coverage Status](https://coveralls.io/repos/shahradelahi/easy-http/badge.png?branch=master)](https://coveralls.io/r/shahradelahi/easy-http?branch=master)
[![Code Quality](https://img.shields.io/scrutinizer/g/shahradelahi/easy-http/master.svg?style=flat)](https://scrutinizer-ci.com/g/shahradelahi/easy-http/?b=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/shahradelahi/easy-http.svg)](https://packagist.org/packages/shahradelahi/easy-http)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/shahradelahi/easy-http.svg)](https://github.com/shahradelahi/easy-http/LICENSE)

EasyHttp is a lightweight HTTP client that is easy to use and integrates with your existing PHP application.

* Simple interface for building query strings, headers, and body.
* Supports all HTTP methods, and supports streaming of large files.
* **No dependency**, no need to install any third-party libraries.
* Supports multiple/bulk requests and downloads large files.
* And much more!

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

=========

### Documentation

We've created some sample of usage in below and if you have questions or want a new feature, please feel free to
open [an issue](https://github.com/shahradelahi/easy-http/issues/new).

* [Send simple request](/docs/send-request.md)
* [Breakdown of a large request into pieces](/docs/breakdown-large-request.md)
* [Send multiple requests at once](/docs/send-multiple-requests.md)
* [Download large files](/examples/download/download-large-file.php)

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
