<?php

namespace app\components\web;

/**
 * @inheritdoc
 * Dots to underline in $_GET replacing hack
 */
class Request extends \yii\web\Request
{
    /**
     * @inheritdoc
     * Refill $_GET array without replacing dot to underline character in URL variable names
     */
    public function init()
    {
        parent::init();
        $_GET = $this->parseQueryString($_SERVER['QUERY_STRING']);
    }

    /**
     *  $_GET or $_POST (re)parse to get original variable names with '.' character
     * Be carefull!
     * @see http://stackoverflow.com/questions/68651/get-php-to-stop-replacing-characters-in-get-or-post-arrays
     */
    protected function parseQueryString($data)
    {
        $data = rawurldecode($data);        //be carefull!
        $pattern = '/(?:^|(?<=&))[^=&\[]*[^=&\[]*/';        // '/(?:^|(?<=&))[^=[]+/';
        $data = preg_replace_callback($pattern, function ($match){
            return bin2hex(urldecode($match[0]));
        }, $data);
        parse_str($data, $values);

        return array_combine(array_map('hex2bin', array_keys($values)), $values);
    }

}
