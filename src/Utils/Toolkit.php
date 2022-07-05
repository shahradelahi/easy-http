<?php

namespace EasyHttp\Utils;

/**
 * Toolkit class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class Toolkit
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

    /**
     * Generates a random string
     *
     * @param int $length The length of the string
     * @return string
     */
    public static function randomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Make Json string pretty
     *
     * @param string $json The json string
     * @return string
     */
    public static function prettyJson(string $json): string
    {
        return json_encode(json_decode($json), JSON_PRETTY_PRINT);
    }

    /**
     * Convert bytes to human-readable format
     *
     * @param int $bytes The bytes
     * @param bool $binaryPrefix Whether to use binary prefixes
     * @return string
     */
    public static function bytesToHuman(int $bytes, bool $binaryPrefix = true): string
    {
        if ($binaryPrefix) {
            $unit = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            if ($bytes == 0) {
                return '0 ' . $unit[0];
            }

            return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . ' ' . ($unit[$i] ?? 'B');
        } else {
            $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
            if ($bytes == 0) {
                return '0 ' . $unit[0];
            }

            return @round($bytes / pow(1000, ($i = floor(log($bytes, 1000)))), 2) . ' ' . ($unit[$i] ?? 'B');
        }
    }

    /**
     * Validate insensitive case string
     *
     * @param string $string The string
     * @param string $value The second value to compare with
     * @return bool
     */
    public static function insensitiveString(string $string, string $value): bool
    {
        return (bool)preg_match_all('/' . $value . '/i', $string);
    }

	/**
	 * Millisecond sleep
	 *
	 * @param int $milliseconds The milliseconds
	 * @return void
	 */
	public static function sleep(int $milliseconds): void
	{
		usleep($milliseconds * 1000);
	}

	/**
	 * Get current time in milliseconds
	 *
	 * @return int
	 */
	public static function time(): int
	{
		return (int)(microtime(true) * 1000);
	}

	/**
	 * Helper to convert a binary to a string of '0' and '1'.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function sprintB(string $string): string
	{
		$return = '';
		$strLen = strlen($string);
		for ($i = 0; $i < $strLen; $i++) {
			$return .= sprintf('%08b', ord($string[$i]));
		}

		return $return;
	}

}