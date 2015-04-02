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
     * Array of additional HTTP headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Cache of CURL connection info
     *
     * @var array
     */
    protected $info = [];

    /**
     *
     * @var array
     */
    protected $curl_errno = 0;

    /**
     *
     * @var null
     */
    protected $callback = null;

    /**
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
     * Array of CURL options, used in this connection
     *
     * @var array
     */
    protected $options = [];

    /**
     * The number of seconds to wait while trying to connect
     * Use 0 to wait indefinitely
     *
     * @var int
     */
    protected $connectTimeout = 5;

    /**
     *  The maximum number of seconds to allow cURL functions to execute
     *
     * @var int
     */
    protected $timeout = 5;


    public function __construct($resource, \Closure $callback)
    {
        if (empty($resource)) {
            throw new Exception("URL is empty", Exception::URL_IS_EMPTY);
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

        // init curl
        $this->curl = curl_init($resource);

        // set default options
        $default = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => false,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT        => $this->timeout,
        ];
        $this->pushOpt($default);

        // fix callback-function
        $this->callback = $callback;

        return $this;
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function getResponse()
    {
        return $this->response;
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
     * Add POST data to connection
     *
     * @param $data
     * @return $this
     */
    public function setPost($data) {
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $data);

        return $this;
    }

    /**
     * Execute this Stream
     */
    public function exec()
    {
        $errno = 0;

        if (($response = curl_exec($this->curl)) === false) {
            $errno = curl_errno($this->curl);
        }

        return $this->setResponse($errno, $response);
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

        $this->raw = call_user_func($this->callback, $this);

        return $this->raw;
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
            return curl_error($this->curl);
        }

        return false;
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
     * File for save cookie data
     *
     * @param $file
     * @return $this
     * @throws Exception
     */
    public function setCookie($file)
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
        $this->setOpt(CURLOPT_HTTPHEADER, $this->headers);

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
        if ($this->isResource()) {
            curl_close($this->curl);
        }
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