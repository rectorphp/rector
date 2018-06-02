<?php declare(strict_types=1);

namespace SomeNamespace;

use Rector\Nette\Tests\Rector\Utils\MagicMethodRector\Source\SomeObject;

/**
 * @method getValue()
 * @method int getAnotherValue()
 */
class SomeClassExtendingNetteObject extends SomeObject
{
    private $value;
    private $anotherValue;
}
