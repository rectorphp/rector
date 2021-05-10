<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210510\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use function in_array;
use function is_string;
use function strtolower;
final class Charset extends Declaration
{
    public const CHARSETS = ['latin1', 'utf-8', 'utf-8-bom', 'utf-16be', 'utf-16le'];
    /**
     * @inheritdoc
     */
    public function validateValue($value) : void
    {
        if (is_string($value) === \false || in_array(strtolower($value), self::CHARSETS) === \false) {
            throw new InvalidValue($this->getStringValue(), $this->getName());
        }
    }
    public function getName() : string
    {
        return 'charset';
    }
}
