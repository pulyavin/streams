# streams
PHP wrapper for multi curl

```php
use pulyavin\streams\Stream;
use pulyavin\streams\Streamer;

$callbackSuccess = function ($stream) {
    /** @var $stream Stream */
    var_dump($stream->getInfo("url"));

    // to use in Streamer::map
    return $stream->getResponse();
};

$callbackFail = function ($stream) {
    /** @var $stream Stream */
    var_dump($stream->getError());
};

$callbackSymfony = function ($stream) {
    /** @var $stream Stream */
    var_dump($stream->getError());
    var_dump($stream->getOpt());

    // to use in Streamer::map
    return $stream->getResponse();
};

try {
    // new Stream object
    $stream1 = new Stream("http://laravel.com", $callbackSuccess, $callbackFail);

    // new Stream object with additional GET params
    // URL will be http://phalconphp.com?page=download&lang=eng
    $stream2 = new Stream([
        "http://phalconphp.com",
        [
            'page' => 'download',
            'lang' => 'eng'
        ]
    ], $callbackSuccess);

    // new Stream object with additional CURL-options
    $stream3 = new Stream("http://symfony.com", $callbackSymfony);
    $stream3->setOpt(CURLOPT_HEADER, true);
    $stream3->setOpt(CURLOPT_ENCODING, "gzip, deflate");
    // additional Stream tools
    $stream3->setProxy("56.156.50.69:80", "username", "password");
    $stream3->saveCookie("./cookie.txt");
    $stream3->setTimeout(3, 3);

    // or this way...
    $stream4 = new Stream("http://yiiframework.com", $callbackSuccess);
    $stream4->pushOpt([
        CURLOPT_HEADER         => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 5,
    ]);

    $stream5 = new Stream("http://www.codeigniter.com", $callbackSuccess);
    // set some cookie params
    $stream5->setCookie("name", "John");
    $stream5->setCookie("last", "1418197053");
    // push some cookie params
    $stream5->pushCookie([
        'banner' => '1',
        'guest'  => '1',
    ]);
    // and we got such HTTP headers
    // Cookie: name=John; last=1418197053; banner=1; guest=1;

    $stream6 = new Stream("http://kohanaframework.org",
        function($stream) {
            /** @var $stream Stream */
            var_dump($stream->getInfo("url"));
        
            // to use in Streamer::map
            return $stream->getResponse();
        },
        function($stream) {
            /** @var $stream Stream */
            var_dump($stream->getError());
        }
    );
    
    $stream7 = new Stream("http://cakephp.org", $callbackSuccess);
    $stream8 = new Stream("http://framework.zend.com", $callbackSuccess);
    
    // add pool of Streams in constructor
    $streamer = new Streamer([$stream1, $stream2, $stream3]);
    // or add them separately using method add() in Streamer-object
    $streamer->setStream($stream4);
    $streamer->setStream($stream5);
    // or push
    $streamer->pushStream([$stream6, $stream7, $stream8]);

    // Streams execution
    $streamer->exec();

    $map = $streamer->map(function ($response) {
        return strlen($response);
    });

    var_dump($map);
} catch (pulyavin\streams\Exception $e) {
    echo $e->getMessage();
}
```

You can use Stream as a single object, without putting it in pool of Streams

```php
use pulyavin\streams\Stream;

$search = "some line";

try {
    $stream = new Stream([
        "http://google.com",
        [
            'q'       => 'Hello world!',
            'channel' => 'fs'
        ]
    ]);

    $stream->setOpt(CURLOPT_HEADER, true);
    $stream->setOpt(CURLOPT_ENCODING, "gzip, deflate");

    $stream->pushOpt([
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 5,
    ]);

    $stream->setSsl("./certificate.cer");

    $stream->setAgent("Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)");

    $stream->setReferer("http://yandex.ru/");

    $stream->setProxy("56.156.50.69:80", "username", "password");

    $stream->saveCookie("./cookies.txt");

    // HTTP verb is POST, and POST data is
    $stream->setPost('client' , 'linux');
    $stream->setPost('ie' , 'utf-8');
    $stream->pushPost([
        'oe'     => 'utf-8',
        'ei'     => 'L6gzVZ31CAeZ4cTlpICgBA',
    ]);

    $stream->setHeader("X-PARAM-FIRST", "first");
    $stream->setHeader("X-PARAM-SECOND", "second");

    $stream->pushHeader([
        'X-PARAM-THIRD'  => 'third',
        'X-PARAM-FOURTH' => 'fourth',
    ]);

    $response = $stream->exec();
    
    var_dump($response);
}
catch (pulyavin\streams\Exception $e) {
    echo $e->getMessage();
}
```
