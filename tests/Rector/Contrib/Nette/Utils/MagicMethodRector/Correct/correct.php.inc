<?php declare(strict_types=1);

namespace SomeNamespace;

use Nette\Object;

/**
 * @method getValue()
 */
class SomeClass extends Object
{
    public function getValue()
    {
        return $this->value;
    }
}
