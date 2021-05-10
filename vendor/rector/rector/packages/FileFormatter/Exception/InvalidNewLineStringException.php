<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidNewLineStringException extends \InvalidArgumentException
{
    /**
     * @return $this
     */
    public static function fromString(string $string)
    {
        return new self(\sprintf('"%s" is not a valid new-line character sequence.', $string));
    }
    /**
     * @return $this
     */
    public static function create(string $message)
    {
        return new self($message);
    }
}
