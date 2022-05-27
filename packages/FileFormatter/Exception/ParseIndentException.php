<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use UnexpectedValueException;
final class ParseIndentException extends UnexpectedValueException
{
    public function __construct(string $string)
    {
        $message = \sprintf('The content "%s" could not be parsed', $string);
        parent::__construct($message);
    }
}
