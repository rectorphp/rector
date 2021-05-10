<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
final class InvalidIndentStyleException extends \InvalidArgumentException
{
    /**
     * @param array<int, string> $allowedStyles
     * @return $this
     */
    public static function fromStyleAndAllowedStyles(string $style, array $allowedStyles)
    {
        $message = \sprintf('Given style "%s" is not allowed. Allowed are "%s"', $style, \implode(' ', $allowedStyles));
        return new self($message);
    }
}
