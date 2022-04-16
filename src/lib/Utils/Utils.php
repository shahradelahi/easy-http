<?php

namespace EasyHttp\Utils;

/**
 * Utils
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class Utils
{

    /**
     * Builds url from array of parameters
     *
     * @param string $base The base url
     * @param array $body Each segment of the url
     * @param array $params The query string parameters
     * @return string
     */
    public static function buildUrl(string $base, array $body, array $params): string
    {
        $url = $base;
        $url .= '/' . implode('/', $body);
        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

}