<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidIndentStringException extends \InvalidArgumentException
{
    public function __construct(string $value)
    {
        $message = \sprintf('The "%s" is not valid indentation', $value);
        parent::__construct($message);
    }
}
