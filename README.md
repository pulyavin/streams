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
    // new Stream object with native URL 
    $stream1 = new Stream("http://laravel.com", $callback);
    
    // new Stream object with CURL-options
    $stream2 = new Stream([
        CURLOPT_URL => "http://symfony.com",
        CURLOPT_HEADER => true
    ], $callbackSymfony);

    $stream3 = new Stream([CURLOPT_URL => "http://yiiframework.com"], $callback);
    $stream4 = new Stream([CURLOPT_URL => "http://www.phalconphp.com"], $callback);
    $stream5 = new Stream([CURLOPT_URL => "http://www.codeigniter.com"], $callback);
    $stream6 = new Stream([CURLOPT_URL => "http://kohanaframework.org"], $callback);

    // add pool of Streams in constructor
    $streamer = new Streamer([$stream1, $stream2, $stream3, $stream4]);
    // or add with method add() in object
    $streamer->add($stream5);
    $streamer->add($stream6);
    
    // execution Streams
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
