<?php
namespace pulyavin\streams;

class Stream
{
    private $curl;
    private $info = [];
    private $callback;
    public $content;
    public $setopt;

    public function __construct(array $constants = [], \Closure $callback) {
        // инициализируем curl
        $this->curl = curl_init();

        // устанавливаем переданные значения
        $setopt = [
            CURLOPT_RETURNTRANSFER => true
        ];
        $setopt += $constants;
        $this->setopt = $setopt;
        $this->pushOpt($this->setopt);

        // запоминаем callback-обработчик
        $this->callback = $callback;
    }

    /**
     * Устанавливает одну константу
     * 
     * @param $constant
     * @param $value
     */
    public function setOpt($constant, $value) {
        curl_setopt($this->curl, $constant, $value);
    }

    /**
     * Устанавливает массив констант
     * 
     * @param array $constants
     */
    public function pushOpt(array $constants) {
        curl_setopt_array($this->curl, $constants);
    }

    /**
     * Возвращает объект curl
     * 
     * @return resource
     */
    public function getResource() {
        return $this->curl;
    }

    /**
     * Вызывает callback-функцию
     * 
     * @return resource
     */
    public function call(self $stream) {
        call_user_func($this->callback, $stream);
    }

    public function getInfo($param = null) {
        if (empty($this->info)) {
            $this->info = curl_getinfo($this->curl);
        }

        if (empty($param)) {
            return $this->info;
        }
        else {
            return isset($this->info[$param]) ? $this->info[$param] : null;
        }
    }

    /**
     * Закрывает curl
     * 
     * @return resource
     */
    public function close() {
        if ($this->isResource()) {
            curl_close($this->curl);
        }
    }

    public function isResource() {
        return get_resource_type($this->curl) == "curl";
    }

    public function __destruct() {
        $this->close();
    }
}
