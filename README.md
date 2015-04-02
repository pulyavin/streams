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
    // new Stream object
    $stream1 = new Stream("http://laravel.com", $callback);

    // new Stream object with additional CURL-options
    $stream2 = new Stream("http://symfony.com", $callbackSymfony);
    $stream2->setOpt(CURLOPT_HEADER, true);
    $stream2->setOpt(CURLOPT_ENCODING, "gzip, deflate");
    // additional Stream params
    $stream2->setProxy("56.156.50.69:80", "username", "password");
    $stream2->setCookie("./cookie.txt");
    
    // or this way...
    $stream3 = new Stream("http://yiiframework.com", $callback);
    $stream3->pushOpt([
        CURLOPT_HEADER         => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 5,
    ]);

    $stream4 = new Stream("http://phalconphp.com", $callback);
    $stream5 = new Stream("http://www.codeigniter.com", $callback);
    $stream6 = new Stream("http://kohanaframework.org", $callback);

    // add pool of Streams in constructor
    $streamer = new Streamer([$stream1, $stream2, $stream3, $stream4]);
    // or add them separately using method add() in Streamer-object
    $streamer->add($stream5);
    $streamer->add($stream6);

    // Streams execution
    $streamer->exec();

    $map = $streamer->map(function ($raw) {
        return strlen($raw);
    });

    var_dump($map);
}
catch (Exception $e) {
    echo $e->getMessage();
}
```
