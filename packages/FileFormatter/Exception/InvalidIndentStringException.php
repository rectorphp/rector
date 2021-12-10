<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidIndentStringException extends \InvalidArgumentException
{
    public static function fromString(string $string) : self
    {
        $message = \sprintf('This is not valid indentation "%s"', $string);
        return new self($message);
    }
}
