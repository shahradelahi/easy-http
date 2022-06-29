<?php

function generateUpToDateMimeArray(string $url): array
{
	$s = array();
	foreach (@explode("\n", @file_get_contents($url)) as $x) {
		if (isset($x[0]) && $x[0] !== '#' && preg_match_all('#([^\s]+)#', $x, $out) && isset($out[1]) && ($c = count($out[1])) > 1) {
			for ($i = 1; $i < $c; $i++) {
				$s[$out[1][$i]] = $out[1][0];
			}
		}
	}
	return $s;
}

$data = generateUpToDateMimeArray('https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');
echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';