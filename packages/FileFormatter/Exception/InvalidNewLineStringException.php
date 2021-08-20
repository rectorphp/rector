<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidNewLineStringException extends \InvalidArgumentException
{
    /**
     * @param string $string
     */
    public static function fromString($string) : self
    {
        return new self(\sprintf('"%s" is not a valid new-line character sequence.', $string));
    }
    /**
     * @param string $message
     */
    public static function create($message) : self
    {
        return new self($message);
    }
}
