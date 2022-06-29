<?php

namespace EasyHttp\Exceptions;

/**
 * BadResponseException class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class BadResponseException extends \Exception
{

	protected $message = 'Bad response from server';

}