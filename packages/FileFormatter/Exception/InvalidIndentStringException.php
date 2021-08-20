<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidIndentStringException extends \InvalidArgumentException
{
    /**
     * @return $this
     * @param string $string
     */
    public static function fromString($string)
    {
        $message = \sprintf('This is not valid indentation "%s"', $string);
        return new self($message);
    }
}
