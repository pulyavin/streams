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
    private $curl = null;

    /**
     *
     * @var array
     */
    private $info = [];

    /**
     *
     * @var array
     */
    private $error = [];

    /**
     *
     * @var array
     */
    private $callback = null;

    /**
     *
     * @var array
     */
    private $content = null;

    /**
     *
     * @var array
     */
    private $raw = null;

    /**
     *
     * @var array
     */
    private $options = [];

    public function __construct(array $constants = [], \Closure $callback)
    {
        // инициализируем curl
        $this->curl = curl_init();

        // устанавливаем переданные значения
        $options = [
            CURLOPT_RETURNTRANSFER => true
        ];
        $options += $constants;
        $this->options = $options;
        $this->pushOpt($this->options);

        // запоминаем callback-обработчик
        $this->callback = $callback;
    }

    /**
     * Устанавливает одну константу
     *
     * @param $constant
     * @param $value
     */
    public function setOpt($constant, $value)
    {
        curl_setopt($this->curl, $constant, $value);
    }

    /**
     * Устанавливает массив констант
     *
     * @param array $constants
     */
    public function pushOpt(array $constants)
    {
        curl_setopt_array($this->curl, $constants);
    }

    /**
     * Возвращает объект curl
     *
     * @param bool $boolean
     * @return resource
     */
    public function getResource($boolean = false)
    {
        return $boolean ? (int)$this->curl : $this->curl;
    }

    /**
     * Вызывает callback-функцию
     *
     * @param $result
     * @param $content
     * @return resource
     */
    public function call($result, $content)
    {
        $this->content = $content;

        $this->raw = call_user_func($this->callback, $this);
    }

    /**
     * Возвращает данные функции curl_getinfo()
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
     * Возвращает ошибку curl
     *
     * @return array|bool
     */
    public function getError() {
        if (empty($this->error)) {
            $this->error = [curl_errno($this->curl), curl_error($this->curl)];
        }

        if (empty($this->error[0])) {
            return false;
        } else {
            return $this->error;
        }
    }

    /**
     * Возвращает установленные в curl параметры
     *
     * @param null $param
     * @return array|null
     */
    public function getOpt($param = null) {
        if (empty($param)) {
            return $this->options;
        } else {
            return isset($this->options[$param]) ? $this->options[$param] : null;
        }
    }

    public function getRaw() {
        return $this->raw;
    }

    public function getContent() {
        return $this->content;
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

    function __clone()
    {
        $this->curl = curl_copy_handle($this->curl);
    }
}