<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector;

use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Source\InterfaceWithPhpdocMethod;

class SkipPhpdocMethod implements InterfaceWithPhpdocMethod
{
    public function someMethod(?string $param): self
    {
        return $this;
    }
}

?>
