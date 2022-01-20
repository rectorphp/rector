<?php

namespace GlobalInstall;

use App\ExceptionInterface;

class MyException extends \RuntimeException implements ExceptionInterface
{
    public static function forAnything(string $content)
    {
        return new static($content);
    }
}
