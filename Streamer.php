<?php namespace pulyavin\streams;

/**
 * Class Streamer
 * @package pulyavin\streams
 */
class Streamer
{
    /**
     * Multi Curl resource handler
     * @var array
     */
    protected $curl;

    /**
     * Pool objects of Stream
     *
     * @var array
     */
    protected $streams = [];

    public function __construct($streams)
    {
        if (!is_array($streams)) {
            $streams = [$streams];
        }

        // инициализируем curl
        $this->curl = curl_multi_init();

        foreach ($streams as $stream) {
            /** @var $stream Stream */
            $this->add($stream);
        }
    }

    /**
     * Add new Stream in pool
     *
     * @param Stream $stream
     * @throws Exception
     */
    public function add(Stream $stream)
    {
        /** @var $stream Stream */
        if (!$stream->isResource()) {
            throw new Exception("Is not a valid cURL Handle resource", Exception::INVALID_CURL);
        }

        curl_multi_add_handle($this->curl, $stream->getResource());
        $this->streams[$stream->getResource(true)] = $stream;
    }

    /**
     * Execute multi curl
     *
     * @return boolean
     * @throws Exception
     */
    public function exec()
    {
        if (!$this->isResource()) {
            throw new Exception("Is not a valid cURL Multi Handle resource", Exception::INVALID_MULTI_CURL);
        }

        if (empty($this->streams)) {
            throw new Exception("Pull of streams is empty", Exception::PULL_IS_EMPTY);
        }

        $running = $messages = 0;

        do {
            // сколько ещё необработанных потоков
            if (($error = curl_multi_exec($this->curl, $running)) != 0) {
                throw new Exception(curl_multi_strerror($error), Exception::MULTI_CURL_ERROR);
            }

            // если готовые потоки, и сколько их в эту итерацию
            do {
                if ($read = curl_multi_info_read($this->curl, $messages)) {
                    $handle = $read['handle'];
                    /** @var $stream Stream */
                    $stream = $this->streams[(int)$handle];
                    $stream->setResponse($read['result'], curl_multi_getcontent($handle));
                }
            } while ($messages);

            // помолимся, братья и сестры...
            usleep(1000);

        } while ($running);

        // закрываем дескрипторы
        $this->closeResource();

        return $this;
    }


    /**
     * Applies the callback to the raw of Streams
     *
     * @param callable $callback
     * @return array
     */
    public function map(\Closure $callback)
    {
        $map = [];

        foreach ($this->streams as $stream) {
            /** @var $stream Stream */
            $map[] = call_user_func($callback, $stream->getRaw());
        }

        return $map;
    }

    /**
     * Destroy curl resources
     */
    protected function closeResource()
    {
        if ($this->isResource()) {
            foreach ($this->streams as $stream) {
                /** @var $stream Stream */
                if ($stream->isResource()) {
                    curl_multi_remove_handle($this->curl, $stream->getResource());
                    $stream->closeResource();
                }
            }

            curl_multi_close($this->curl);
        }
    }

    /**
     * Is it curl resource?
     *
     * @return bool
     */
    public function isResource()
    {
        return get_resource_type($this->curl) == "curl_multi";
    }

    public function __destruct()
    {
        $this->closeResource();
    }
}