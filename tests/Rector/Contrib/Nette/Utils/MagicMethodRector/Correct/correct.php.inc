<?php declare(strict_types=1);

namespace SomeNamespace;

use Rector\Tests\Rector\Contrib\Nette\Utils\MagicMethodRector\Source\SomeObject;

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
