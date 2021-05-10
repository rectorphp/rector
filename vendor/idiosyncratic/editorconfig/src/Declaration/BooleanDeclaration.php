<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210510\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use function is_bool;
abstract class BooleanDeclaration extends Declaration
{
    /**
     * @inheritdoc
     */
    public function validateValue($value) : void
    {
        if (is_bool($value) === \false) {
            throw new InvalidValue($this->getStringValue(), $this->getName());
        }
    }
}
