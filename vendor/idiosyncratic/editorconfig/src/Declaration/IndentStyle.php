<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20211020\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use function in_array;
use function is_string;
use function strtolower;
final class IndentStyle extends \RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration\Declaration
{
    public function getName() : string
    {
        return 'indent_style';
    }
    /**
     * @inheritdoc
     */
    public function validateValue($value) : void
    {
        if (\is_string($value) === \false || \in_array(\strtolower($value), ['tab', 'space']) === \false) {
            throw new \RectorPrefix20211020\Idiosyncratic\EditorConfig\Exception\InvalidValue($this->getStringValue(), $this->getName());
        }
    }
}
