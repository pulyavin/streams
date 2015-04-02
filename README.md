# streams
PHP wrapper for multi curl

```php
use pulyavin\streams\Stream;
use pulyavin\streams\Streamer;
use pulyavin\streams\Exception;

$callback = function($stream) {
    /** @var $stream Stream */
    var_dump($stream->getInfo("url"));
    
    // to use in Streamer::map
    return $stream->getContent();
};

$callbackSymfony = function($stream) {
    /** @var $stream Stream */
    var_dump($stream->getError());
    var_dump($stream->getOpt());
    
    // to use in Streamer::map
    return $stream->getContent();
};

try {
    $stream1 = new Stream([CURLOPT_URL => "http://laravel.com"], $callback);
    $stream2 = new Stream([CURLOPT_URL => "http://yiiframework.com"], $callback);
    $stream3 = new Stream([CURLOPT_URL => "http://symfony.com"], $callbackSymfony);
    $stream4 = new Stream([CURLOPT_URL => "http://www.phalconphp.com"], $callback);
    $stream5 = new Stream([CURLOPT_URL => "http://www.codeigniter.com"], $callback);
    $stream6 = new Stream([CURLOPT_URL => "http://kohanaframework.org"], $callback);

    $streamer = new Streamer([$stream1, $stream2, $stream3, $stream4, $stream5, $stream6]);
    $streamer->exec();
    
    $map = $streamer->map(function($raw) {
        return strlen($raw);
    });
    
    var_dump($map);
}
catch (Exception $e) {
    echo $e->getMessage();
}
```
