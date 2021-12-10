<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidIndentSizeException extends \InvalidArgumentException
{
    public static function fromSizeAndMinimumSize(int $size, int $minimumSize) : self
    {
        $message = \sprintf('Size %d must be greater than %d', $size, $minimumSize);
        return new self($message);
    }
}
