<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidNewLineStringException extends \InvalidArgumentException
{
    /**
     * @return $this
     * @param string $string
     */
    public static function fromString($string)
    {
        return new self(\sprintf('"%s" is not a valid new-line character sequence.', $string));
    }
    /**
     * @return $this
     * @param string $message
     */
    public static function create($message)
    {
        return new self($message);
    }
}
