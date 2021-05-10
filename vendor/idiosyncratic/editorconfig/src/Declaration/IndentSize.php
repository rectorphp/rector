<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210510\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use function is_int;
final class IndentSize extends Declaration
{
    public function getName() : string
    {
        return 'indent_size';
    }
    /**
     * @inheritdoc
     */
    public function validateValue($value) : void
    {
        if ($value !== 'tab' && (is_int($value) === \false || $value < 1 === \true)) {
            throw new InvalidValue($this->getStringValue(), $this->getName());
        }
    }
}
