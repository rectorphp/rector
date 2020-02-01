<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\StaticProperty;

class SkipInterfaceRequired implements ParentInterface
{
    private $name;
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}

interface ParentInterface
{
    public function setName(string $name): void;
}
