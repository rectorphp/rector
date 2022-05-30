<?php

namespace RectorPrefix20220530\Psr\Cache;

/**
 * Exception interface for invalid cache arguments.
 *
 * Any time an invalid argument is passed into a method it must throw an
 * exception class which implements Psr\Cache\InvalidArgumentException.
 */
interface InvalidArgumentException extends \RectorPrefix20220530\Psr\Cache\CacheException
{
}
