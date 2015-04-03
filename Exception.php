<?php namespace pulyavin\streams;

class Exception extends \Exception {
    const INVALID_MULTI_CURL = 1;
    const INVALID_CURL = 2;
    const PULL_IS_EMPTY = 3;
    const MULTI_CURL_ERROR = 4;
    const URL_IS_EMPTY = 5;
    const INVALID_COOKIE_FILE = 6;
    const CLONING = 7;
    const NOT_AVAILABLE = 8;
}