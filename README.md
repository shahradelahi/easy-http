> **Help wanted:** If you can improve this library, please do so.
> ***Pull requests are welcome.***

# Easy Http

[![Build Status](https://scrutinizer-ci.com/g/shahradelahi/easy-http/badges/build.png?b=master)](https://scrutinizer-ci.com/g/shahradelahi/easy-http/build-status/master)
[![Coverage Status](https://scrutinizer-ci.com/g/shahradelahi/easy-http/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/shahradelahi/easy-http/?branch=master)
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

[Download installer for Windows](https://getcomposer.org/doc/00-intro.md#installation-windows)

</details>

#### Getting started

```php
$client = new \EasyHttp\HttpClient();
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
* [Upload multiple files](/examples/upload/upload-multiple-files.php)

### License

EasyHttp is licensed under the MIT License - see the [LICENSE](/LICENSE) file for details