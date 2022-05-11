<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Exception;

use InvalidArgumentException;
use Rector\FileFormatter\Enum\IndentType;
final class InvalidIndentStyleException extends \InvalidArgumentException
{
    public function __construct(string $style)
    {
        $allowedIndentTypesString = \implode('", "', [\Rector\FileFormatter\Enum\IndentType::SPACE, \Rector\FileFormatter\Enum\IndentType::TAB]);
        $message = \sprintf('Given style "%s" is not allowed. Allowed are "%s"', $style, $allowedIndentTypesString);
        parent::__construct($message);
    }
}
