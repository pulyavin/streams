# streams
PHP wrapper for multi curl

```php
$callback = function($stream) {
    var_dump($stream);
};

$callbackN = function($stream) {
    var_dump($stream->getInfo());
};

$stream1 = new pulyavin\streams\Stream([CURLOPT_URL => "http://laravel.com"], $callback);
$stream2 = new pulyavin\streams\Stream([CURLOPT_URL => "http://yiiframework.com"], $callback);
$stream3 = new pulyavin\streams\Stream([CURLOPT_URL => "http://symfony.com"], $callbackN);
$stream4 = new pulyavin\streams\Stream([CURLOPT_URL => "http://www.phalconphp.com"], $callback);

$streamer = new pulyavin\streams\Streamer([$stream1, $stream2, $stream3, $stream4]);
$streamer->exec();
```
