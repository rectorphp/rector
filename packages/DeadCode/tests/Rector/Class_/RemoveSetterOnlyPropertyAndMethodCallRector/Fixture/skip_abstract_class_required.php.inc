<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\StaticProperty;

class SkipAbstractClassRequired extends AbstractClassRequiring
{
    private $name;
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}

abstract class AbstractClassRequiring
{
    protected abstract function setName(string $name);
}
