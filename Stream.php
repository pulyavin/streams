<?php namespace pulyavin\streams;

/**
 * Class Stream
 * @package pulyavin\streams
 */
class Stream
{
    /**
     * Curl resource handler
     *
     * @var null|resource
     */
    protected $curl = null;

    /**
     * Arrays of additional HTTP headers, cookies, options and POST data, used in this connection
     *
     * @var array
     */
    protected $headers = [];
    protected $cookies = [];
    protected $options = [];
    protected $post = [];

    /**
     * Cache of CURL connection info
     *
     * @var array
     */
    protected $info = [];

    /**
     * Cache of CURL error data
     *
     * @var array
     */
    protected $curl_errno = 0;
    protected $curl_error = null;

    /**
     * Callback handler
     *
     * @var null
     */
    protected $callback = null;

    /**
     * Cache of curl response
     *
     * @var null
     */
    protected $response = null;

    /**
     * The raw returned by the callback function
     *
     * @var null
     */
    protected $raw = null;

    /**
     * The number of seconds to wait while trying to connect
     * Use 0 to wait indefinitely
     *
     * @var int
     */
    protected $connect_timeout = 5;

    /**
     *  The maximum number of seconds to allow cURL functions to execute
     *
     * @var int
     */
    protected $timeout = 5;


    public function __construct($resource, \Closure $callback)
    {
        if (!function_exists('curl_init')) {
            throw new Exception('Curl functions are not available', Exception::NOT_AVAILABLE);
        }

        if (empty($resource)) {
            throw new Exception('URL is empty', Exception::URL_IS_EMPTY);
        }

        // $resource consist of URL and additional GET params
        if (is_array($resource)) {
            $params = isset($resource[1]) ? $resource[1] : [];
            $resource = isset($resource[0]) ? $resource[0] : null;

            if (empty($resource)) {
                throw new Exception("URL is empty", Exception::URL_IS_EMPTY);
            }

            $query = parse_url($resource, PHP_URL_QUERY);
            parse_str($query, $output);
            $params += $output;

            if (!empty($params)) {
                $resource = current(explode("?", $resource)) . "?" . http_build_query($params);
            }
        }

        // initialization of curl
        $this->curl = curl_init($resource);

        // set default options
        $default = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => false,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
        ];
        $this->pushOpt($default);

        // fix callback-function
        $this->callback = $callback;

        return $this;
    }

    /**
     * Execute curl this Stream
     *
     * @return mixed|null
     * @throws Exception
     */
    public function exec()
    {
        if (!$this->isResource()) {
            throw new Exception("Is not a valid cURL Handle resource", Exception::INVALID_CURL);
        }

        $errno = 0;

        if (($response = curl_exec($this->curl)) === false) {
            $this->closeResource();

            throw new Exception(curl_error($this->curl), curl_errno($this->curl));
        }

        return $this->setResponse(curl_error($this->curl), $response);
    }

    /**
     * Getter of raw, returned by the callback function
     *
     * @return null
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * Getter of CURL response
     *
     * @return null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns CURL handler
     *
     * @param bool $boolean
     * @return resource
     */
    public function getResource($boolean = false)
    {
        return $boolean ? (int)$this->curl : $this->curl;
    }

    /**
     * Returns data of connection
     *
     * @param null $param
     * @return array|mixed|null
     */
    public function getInfo($param = null)
    {
        if (empty($this->info)) {
            $this->info = curl_getinfo($this->curl);
        }

        if (empty($param)) {
            return $this->info;
        } else {
            return isset($this->info[$param]) ? $this->info[$param] : null;
        }
    }

    /**
     * Returns curl error
     *
     * @return string|bool
     */
    public function getError()
    {
        if (!empty($this->curl_errno)) {
            return $this->curl_error;
        }

        return false;
    }

    /**
     * Set connection time and timeout values
     *
     * @param $connect_timeout The number of seconds to wait while trying to connect. Use 0 to wait indefinitely
     * @param $timeout The maximum number of seconds to allow cURL functions to execute
     * @return $this
     */
    public function setTimeout($connect_timeout, $timeout)
    {
        $this->connect_timeout = $connect_timeout;
        $this->timeout = $timeout;

        $this->setOpt(CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        $this->setOpt(CURLOPT_TIMEOUT, $this->timeout);

        return $this;
    }

    /**
     * Set User Agent to HTTP headers
     *
     * @param $agent
     * @return $this
     */
    public function setAgent($agent)
    {
        $this->setOpt(CURLOPT_USERAGENT, $agent);

        return $this;
    }

    /**
     * Set referer to HTTP headers
     *
     * @param $referer
     * @return $this
     */
    public function setReferer($referer)
    {
        $this->setOpt(CURLOPT_REFERER, $referer);

        return $this;
    }

    /**
     * Set response and call callback function
     *
     * @param $errno
     * @param $response
     * @return mixed|null
     */
    public function setResponse($errno, $response)
    {
        $this->response = $response;

        $this->curl_errno = $errno;
        $this->curl_error = curl_error($this->curl);

        $this->raw = call_user_func($this->callback, $this);

        $this->closeResource();

        return $this->raw;
    }

    /**
     * Set up a param value of POST data
     *
     * @param $param
     * @param $value
     * @return $this
     */
    public function setPost($param, $value)
    {
        $this->post = array_merge($this->post, [$param => $value]);

        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $this->post);

        return $this;
    }

    /**
     * Set up an array of POST data
     *
     * @param $params
     * @return $this
     */
    public function pushPost(array $params)
    {
        foreach ($params as $param => $value) {
            $this->setPost($param, $value);
        }

        return $this;
    }

    /**
     * Set up a constant value
     *
     * @param $constant
     * @param $value
     * @return $this
     */
    public function setOpt($constant, $value)
    {
        $this->options[$constant] = $value;
        curl_setopt($this->curl, $constant, $value);

        return $this;
    }

    /**
     * Set up an array of constants
     *
     * @param array $constants
     * @return $this
     */
    public function pushOpt(array $constants)
    {
        $this->options += $constants;
        curl_setopt_array($this->curl, $constants);

        return $this;
    }

    /**
     * Return curl options
     *
     * @param null $param
     * @return array|null
     */
    public function getOpt($param = null)
    {
        if (empty($param)) {
            return $this->options;
        } else {
            return isset($this->options[$param]) ? $this->options[$param] : null;
        }
    }

    /**
     * Use proxy connection for this Stream
     *
     * @param $proxy
     * @param null $login
     * @param null $password
     * @return $this
     */
    public function setProxy($proxy, $login = null, $password = null)
    {
        $this->setOpt(CURLOPT_PROXY, $proxy);

        if (!empty($login)) {
            $auth = $login . ":" . $password;
            $this->setOpt(CURLOPT_PROXYUSERPWD, $auth);
        }

        return $this;
    }

    /**
     * Set up a cookie value
     *
     * @param $param
     * @param $value
     * @return $this
     */
    public function setCookie($param, $value)
    {
        $this->cookies = array_merge($this->cookies, [$param => $value]);

        $cookies = '';
        foreach ($this->cookies as $param => $value) {
            $cookies .= $param . '=' . $value . '; ';
        }

        $this->setOpt(CURLOPT_COOKIE, $cookies);

        return $this;
    }

    /**
     * Set up an array of cookies
     *
     * @param array $params
     * @return $this
     */
    public function pushCookie(array $params)
    {
        foreach ($params as $param => $value) {
            $this->setCookie($param, $value);
        }

        return $this;
    }

    /**
     * File for saving cookie data
     *
     * @param $file
     * @return $this
     * @throws Exception
     */
    public function saveCookie($file)
    {
        if (!file_exists($file)) {
            touch($file);
        }

        if (($file = realpath($file)) == false) {
            throw new Exception("Invalid path to cookie file", Exception::INVALID_COOKIE_FILE);
        }

        $this->setOpt(CURLOPT_COOKIEJAR, $file);
        $this->setOpt(CURLOPT_COOKIEFILE, $file);

        return $this;
    }

    /**
     * Set up a header value
     * @param $param
     * @param $value
     * @return $this
     */
    public function setHeader($param, $value)
    {
        $this->headers = array_merge($this->headers, [$param => $value]);

        $headers = [];
        foreach ($this->headers as $param => $value) {
            $headers[] = $param . ': ' . $value;
        }

        $this->setOpt(CURLOPT_HTTPHEADER, $headers);

        return $this;
    }

    /**
     * Set up an array of headers
     * @param array $headers
     * @return $this
     */
    public function pushHeader(array $headers = [])
    {
        foreach ($headers as $param => $value) {
            $this->setHeader($param, $value);
        }

        return $this;
    }

    /**
     * Destroy curl resource
     */
    public function closeResource()
    {
        if (!$this->isResource()) {
            return false;
        }

        curl_close($this->curl);

        return true;
    }

    /**
     * Is it curl resource?
     *
     * @return bool
     */
    public function isResource()
    {
        return get_resource_type($this->curl) == "curl";
    }

    public function __destruct()
    {
        $this->closeResource();
    }

    public function __clone()
    {
        throw new Exception("Cloning is prohibited", Exception::CLONING);
    }
}