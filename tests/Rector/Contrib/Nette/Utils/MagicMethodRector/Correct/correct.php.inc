<?php declare(strict_types=1);

namespace SomeNamespace;

use Nette\Object;

/**
 *
 */
class SomeClass extends Object
{
    private $value;
    private $anotherValue;
    public function getValue()
    {
        return $this->value;
    }
    public function getAnotherValue()
    {
        return $this->anotherValue;
    }
}
