<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidIndentStyleException extends \InvalidArgumentException
{
    /**
     * @param array<int, string> $allowedStyles
     * @return $this
     * @param string $style
     */
    public static function fromStyleAndAllowedStyles($style, $allowedStyles)
    {
        $message = \sprintf('Given style "%s" is not allowed. Allowed are "%s"', $style, \implode(' ', $allowedStyles));
        return new self($message);
    }
}
