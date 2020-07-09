<?php

/**
 * Return cookie array
 *
 * @param string $headers http response headers
 *
 * @return array
 */
function get_cookie($headers)
{
    preg_match_all('/Set-Cookie: (.+); path/u', $headers, $c);
    foreach ($c[1] as $c_str) {
        list($var, $val) = explode('=', $c_str);
        $c_array[$var] = $val;
    }
    return $c_array;
}
