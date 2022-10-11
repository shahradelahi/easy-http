<?php declare(strict_types=1);

namespace EasyHttp\Exceptions;

/**
 * WebSocketException class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class WebSocketException extends \RuntimeException
{

    public function printStack()
    {
        echo $this->getFile() . ' ' . $this->getLine() . ' ' . $this->getMessage() . PHP_EOL;
        echo $this->getTraceAsString();
    }

}
