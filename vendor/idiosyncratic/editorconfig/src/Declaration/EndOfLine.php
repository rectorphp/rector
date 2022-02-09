<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20220209\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use function in_array;
use function is_string;
use function strtolower;
final class EndOfLine extends \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\Declaration
{
    public const LINE_ENDINGS = ['lf', 'cr', 'crlf'];
    /**
     * @inheritdoc
     */
    public function validateValue($value) : void
    {
        if (\is_string($value) === \false || \in_array(\strtolower($value), self::LINE_ENDINGS) === \false) {
            throw new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Exception\InvalidValue($this->getStringValue(), $this->getName());
        }
    }
    public function getName() : string
    {
        return 'end_of_line';
    }
}
