# streams
PHP wrapper for multi curl

```php
use pulyavin\streams\Stream;
use pulyavin\streams\Streamer;

$callback = function($stream) {
    /** @var $stream Stream */
    var_dump($stream->getInfo("url"));
    
    // to use in Streamer::map
    return $stream->getResponse();
};

$callbackSymfony = function($stream) {
    /** @var $stream Stream */
    var_dump($stream->getError());
    var_dump($stream->getOpt());
    
    // to use in Streamer::map
    return $stream->getResponse();
};

try {
    // new Stream object
    $stream1 = new Stream("http://laravel.com", $callback);

    // new Stream object with additional GET params
    // URL will be http://phalconphp.com?page=download&lang=eng
    $stream2 = new Stream([
        "http://phalconphp.com",
        [
            'page' => 'download',
            'lang' => 'eng'
        ]
    ], $callback);
    
    // new Stream object with additional CURL-options
    $stream3 = new Stream("http://symfony.com", $callbackSymfony);
    $stream3->setOpt(CURLOPT_HEADER, true);
    $stream3->setOpt(CURLOPT_ENCODING, "gzip, deflate");
    // additional Stream tools
    $stream3->setProxy("56.156.50.69:80", "username", "password");
    $stream3->setCookie("./cookie.txt");
    
    // or this way...
    $stream4 = new Stream("http://yiiframework.com", $callback);
    $stream4->pushOpt([
        CURLOPT_HEADER         => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 5,
    ]);

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
catch (pulyavin\streams\Exception $e) {
    echo $e->getMessage();
}
```

You can use Stream as a single object, without putting it in pool of Streams

```php
use pulyavin\streams\Stream;

$search = "some line";

$callback = function ($stream) use ($search) {
    /** @var $stream Stream */
    var_dump($stream->getError());
    var_dump($stream->getOpt());

    // to use in Streamer::map
    return stripos($stream->getResponse(), $search);
};

try {
    $stream = new Stream([
        "http://google.com",
        [
            'q'       => 'Hello world!',
            'channel' => 'fs'
        ]
    ], $callback);

    $stream->setOpt(CURLOPT_HEADER, true);
    $stream->setOpt(CURLOPT_ENCODING, "gzip, deflate");

    $stream->pushOpt([
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 5,
    ]);

    $stream->setAgent("Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)");

    $stream->setReferer("http://yandex.ru/");

    $stream->setProxy("56.156.50.69:80", "username", "password");

    $stream->setCookie("./cookie.txt");

    $stream->setPost([
        'client' => 'linux',
        'ie'     => 'utf-8',
        'oe'     => 'utf-8',
        'ei'     => 'L6gzVZ31CAeZ4cTlpICgBA',
    ]);

    $stream->setHeader("X-PARAM-FIRST", "first");
    $stream->setHeader("X-PARAM-SECOND", "second");

    $stream->pushHeader([
        'X-PARAM-THIRD'  => 'third',
        'X-PARAM-FOURTH' => 'fourth',
    ]);

    $raw = $stream->exec();
    
    var_dump($raw);
}
catch (pulyavin\streams\Exception $e) {
    echo $e->getMessage();
}
```
