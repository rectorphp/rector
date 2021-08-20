<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidIndentSizeException extends \InvalidArgumentException
{
    /**
     * @param int $size
     * @param int $minimumSize
     */
    public static function fromSizeAndMinimumSize($size, $minimumSize) : self
    {
        $message = \sprintf('Size %d must be greater than %d', $size, $minimumSize);
        return new self($message);
    }
}
