<?php
namespace pulyavin\streams;

class Streamer
{
    private $curl;
    private $streams = [];

    public function __construct($streams) {
        if (!is_array($streams)) {
            $streams = [$streams];
        }

        // инициализируем curl
        $this->curl = curl_multi_init();

        foreach ($streams as $stream) {
            $this->add($stream);
        }
    }

    public function add($stream) {
        curl_multi_add_handle($this->curl, $stream->getResource());
        $this->streams[(int)$stream->getResource()] = $stream;
    }

    public function exec() {
        $running = $messages = 0;

        do {
            // сколько ещё необработанных потоков
            curl_multi_exec($this->curl, $running);

            // если готовые потоки, и сколько их в эту итерацию
            do {
                if ($read = curl_multi_info_read($this->curl, $messages)) {
                    $handle = $read['handle'];

                    $this->streams[(int)$handle]->content = curl_multi_getcontent($handle);
                    $this->streams[(int)$handle]->call($this->streams[(int)$handle]);
                }
            } while($messages);

            // помолимся, братья и сестры...
            usleep(1000);

        } while ($running);

        // закрываем дескрипторы
        $this->destroy();
    }

    /**
     * Высвобождаем память
     */
    private function destroy() {
        if (get_resource_type($this->curl) == "curl_multi") {
            foreach ($this->streams as $stream) {
                if ($stream->isResource()) {
                    curl_multi_remove_handle($this->curl, $stream->getResource());
                    $stream->close();
                }
            }

            curl_multi_close($this->curl);
        }
    }

    public function __destruct() {
        $this->destroy();
    }
}
