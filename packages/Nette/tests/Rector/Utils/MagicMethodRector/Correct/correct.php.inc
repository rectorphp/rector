<?php declare(strict_types=1);

namespace SomeNamespace;

use Rector\Nette\Tests\Rector\Utils\MagicMethodRector\Source\SomeObject;

class SomeClassExtendingNetteObject extends SomeObject
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
