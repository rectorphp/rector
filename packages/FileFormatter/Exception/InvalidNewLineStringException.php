<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidNewLineStringException extends \InvalidArgumentException
{
    public static function fromString(string $string) : self
    {
        return new self(\sprintf('"%s" is not a valid new-line character sequence.', $string));
    }
    public static function create(string $message) : self
    {
        return new self($message);
    }
}
