<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use UnexpectedValueException;
final class ParseIndentException extends \UnexpectedValueException
{
    /**
     * @param string $string
     */
    public static function fromString($string) : self
    {
        $message = \sprintf('The content "%s" could not be parsed', $string);
        return new self($message);
    }
}
