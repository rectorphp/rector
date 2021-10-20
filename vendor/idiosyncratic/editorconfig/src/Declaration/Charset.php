<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20211020\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use function in_array;
use function is_string;
use function strtolower;
final class Charset extends \RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration\Declaration
{
    public const CHARSETS = ['latin1', 'utf-8', 'utf-8-bom', 'utf-16be', 'utf-16le'];
    /**
     * @inheritdoc
     */
    public function validateValue($value) : void
    {
        if (\is_string($value) === \false || \in_array(\strtolower($value), self::CHARSETS) === \false) {
            throw new \RectorPrefix20211020\Idiosyncratic\EditorConfig\Exception\InvalidValue($this->getStringValue(), $this->getName());
        }
    }
    public function getName() : string
    {
        return 'charset';
    }
}
