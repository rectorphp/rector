<?php declare(strict_types=1);

namespace SomeNamespace;

use Nette\Object;

/**
 * @method getValue()
 * @method int getAnotherValue()
 */
class SomeClass extends Object
{
    private $value;
    private $anotherValue;
}
