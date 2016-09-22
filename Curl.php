<?php

namespace app\components;

/**
 *  Base curl class, create curl handler (ch) and provide get(), post(), request() methods with result return
 *  TODO: HEAD request
 *
 *  Using these cUrl options:
 *
 *  0:CURLOPT_URL
 *  1:CURLOPT_POSTFIELDS
 *  2:CURLOPT_COOKIE
 *  3:CURLOPT_HEADER
 *  4:CURLOPT_NOBODY
 *  5:CURLOPT_REFERER
 *  6:CURLOPT_COOKIEFILE
 *  7:CURLOPT_USERAGENT
 *  8:CURLOPT_INTERFACE
 *  9:CURLOPT_HTTPHEADER
 *
 *  Curl::setCookieDir(COOKIE_DIR) before usage recommended
 *
 */

class Curl
{
    /**
     * @var string
     */
    protected static $cookieDir;

    /**
     * @var resource CURL handler
     */
    protected $ch;

    /**
     * @var int Error code
     */
    public $error = 0;

    /**
     * @var string Responce Header
     */
    public $header;

    /**
     * @var string Responce Body
     */
    public $body;

    /**
     * @var array info about last request as curl_getinfo()
     */
    public $execInfo;

    /**
     * @var int http code of last request
     */
    public $httpCode;

    /**
     * @var string
     */
    public $cookieName = "common";

    /**
     * @var string
     */
    public $userAgent = "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0";

    /**
     * @var int debug level
     */
    public $debug = 0;

    /**
     * @var int request timeout, s
     */
    public $timeout = 60;

    /**
     * @var int timeout between two requests in microseconds
     */
    public $sleep = 0;

    /**
     * @var bool
     */
    public $responseAsIs = false;

    /**
     * Initialize curl handler
     *
     * @param int $debug
     *
     * @return bool
     */
    public function __construct($debug = 0)
    {
        $this->debug = (int)$debug;
        $this->ch = curl_init();
        $this->setDefaultOptions();
        return true;
    }

    /**
     *  Close $ch
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * Set dir for cookie files
     *
     * @param string $dir
     *
     * @return void
     */
    public static function setCookieDir($dir)
    {
        static::$cookieDir = $dir;
    }

    /**
     * HTTP GET request to the specified $url
     *
     * @param              $url
     * @param array|string $params
     * @param array        $headers
     * @param string       $referer
     *
     * @return array|bool|mixed
     */
    public function get($url, $params = [], $headers = [], $referer = '')
    {
        if (!empty($params)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= (is_string($params)) ? $params : http_build_query($params, '', '&');
        }

        return $this->request('GET', $url, null, $headers, $referer);
    }

    /**
     * HTTP POST request
     *
     * @param              $url
     * @param array|string $params
     * @param array        $headers
     * @param string       $referer
     *
     * @return array|bool|mixed
     */
    public function post($url, $params = [], $headers = [], $referer = '')
    {
        return $this->request('POST', $url, $params, $headers, $referer);
    }


    /**
     *  Drop all request-dependent params to default
     *  Calling it after each request
     *
     * @param int $force
     */
    public function setNullConfig($force = 0)
    {
        $this->setMethod();
        $this->setPost();
        $this->setCookie();
        $this->setReferer();
        $this->setHttpHeader();

        if ($force) {
            $this->setCookieFile();
            $this->setRespHeader();
            $this->setUserAgent();
        }
    }

    /**
     * Set CURLOPT_URL
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
    }

    /**
     * Set CURLOPT_POSTFIELDS
     *
     * @param array|string $post
     */
    public function setPost($post)
    {
        if (is_array($post)) {
            $post = http_build_query($post);
        }

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
    }

    /**
     * Set CURLOPT_COOKIE
     *
     * @param array|string $post
     */
    public function setCookie($str = '')
    {
        curl_setopt($this->ch, CURLOPT_COOKIE, $str);
    }

    /**
     * Set cookie file name
     * Optional. Stateful.
     * May be called one time for several net requests
     *
     * @param string $cookieName
     */
    public function setCookieFile($cookieName)
    {
        if (empty($cookieName)) {
            $cookieName = $this->cookieName;
        }

        $cookieDir = static::$cookieDir;
        if (empty($cookieDir) || !is_dir($cookieDir) || !is_writable($cookieDir)) {
            $cookieDir = sys_get_temp_dir();
        }

        $file = $cookieDir . "/" . $cookieName . ".cookie";

        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $file);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $file);
    }

    /**
     * Set CURLOPT_HEADER
     * Optional. Stateful.
     *
     * @param int $includeHeader
     */
    public function setRespHeader($includeHeader = 0)
    {
        curl_setopt($this->ch, CURLOPT_HEADER, $includeHeader);
    }

    /**
     * Optional. Stateful.
     *
     * @param int $noParse
     */
    public function setRespNoParse($noParse = false)
    {
        $this->responseAsIs = $noParse;
    }

    /**
     * @param string $referer
     */
    public function setReferer($referer = '')
    {
        curl_setopt($this->ch, CURLOPT_REFERER, $referer);
    }

    /**
     * Set CURLOPT_USERAGENT
     * Optional. Stateful.
     *
     * @param string|bool $str UserAgent string (empty or not). If bool - set default value
     */
    public function setUserAgent($str = true)
    {
        if ($str === true) {
            $str = $this->userAgent;
        }
        curl_setopt($this->ch, CURLOPT_USERAGENT, $str);
    }

    /**
     *  Set CURLOPT_TIMEOUT
     *
     * @param int $timeout
     */
    public function setTimeout($timeout = 60)
    {
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
    }


    /**
     *  Set CURLOPT_HTTPHEADER
     *
     * @param array|bool $headers array of headers. If bool - set default value
     */
    protected function setHttpHeader($headers = true)
    {
        if ($headers === true) {
            $headers = [
                //"Accept: text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/webp, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1",
                //"Accept-Language: ru-RU,ru;q=0.9,en;q=0.8",
                "Accept: */*",
                "Accept-Language: en-US,en;q=0.8",
                "Accept-Encoding: gzip, deflate",
                "Connection: Keep-Alive",
                "Keep-Alive: 300",
            ];
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Configure handler, send request and return result
     *
     * @param        $method
     * @param        $url
     * @param array  $params
     * @param array  $headers
     * @param string $referer
     *
     * @return array|bool|mixed
     */
    protected function request($method, $url, $params = [], $headers = [], $referer = '')
    {
        if (empty($url)) {
            return false;
        }

        $this->setMethod($method);
        $this->setUrl($url);
        if ($params) {
            $this->setPost($params);
        }

        $this->setHttpHeader($headers);
        if ($referer) {
            $this->setReferer($referer);
        }

        $this->error = 0;
        $this->execInfo = [];

        $result = $this->exec();

        $this->execInfo = curl_getinfo($this->ch);
        $this->httpCode = $this->execInfo['http_code'];

        $this->sleep();
        $this->setNullConfig();

        if (empty($result) || $this->responseAsIs) {            //return result as is
            return $result;
        } else {                //parse to body and header
            $headerSize = $this->execInfo['header_size'];
            $this->header = trim(substr($result, 0, $headerSize));
            $this->body = trim(substr($result, $headerSize));

            if ($this->debug) {
                echo <<<TXT
                ---------- REQ HEADER -----------
                {$this->execInfo[request_header]}
                ---------------------------------
                ---------- RESP HEADER ----------
                $this->header
                ---------------------------------
TXT;
            }
            if ($this->debug == 2) {
                $body = htmlentities($this->body);
                echo <<<TXT
                ---------- RESP BODY -------------
                $body
                ----------------------------------
TXT;
            }

            return [$this->header, $this->body];
        }

    }


    /**
     * Execute configured handler and return result
     *
     * @return bool|string
     */
    protected function exec()
    {
        $result = curl_exec($this->ch);

        if (curl_errno($this->ch) != 0) {
            $this->error = curl_errno($this->ch);
            $errorMsg = curl_error($this->ch);
            echo "CURL_error: #$this->error ($errorMsg)";        //replace with your favorite logger
            return false;
        }
        return $result;
    }

    /**
     * @param string $method
     */
    protected function setMethod($method = 'GET')
    {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->ch, CURLOPT_NOBODY, 1);
                break;
            case 'GET':
                curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
                break;
            case 'POST':
                curl_setopt($this->ch, CURLOPT_POST, 1);
                break;
            default:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     *  Set request-independent defaults after object created
     */
    protected function setDefaultOptions()
    {
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        //Debug
        curl_setopt($this->ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, $this->debug);
        //Turn off SSL verification
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        //set timeout
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        //others
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 20);
        curl_setopt($this->ch, CURLOPT_ENCODING, '');
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
        //curl_setopt($this->ch, CURLOPT_INTERFACE, $this->ip);

        $this->setUserAgent();
    }

    /**
     *  Sleep between requests
     */
    protected function sleep()
    {
        usleep($this->sleep);
    }

}