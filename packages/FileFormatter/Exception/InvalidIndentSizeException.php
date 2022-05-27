<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidIndentSizeException extends InvalidArgumentException
{
    public function __construct(int $size, int $minimumSize)
    {
        $message = \sprintf('Size %d must be greater than %d', $size, $minimumSize);
        parent::__construct($message);
    }
}
