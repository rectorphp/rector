<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Source\AbstractClassRequiringAbstractMethod;

class SkipAbstractClassRequired extends AbstractClassRequiringAbstractMethod
{
    private $name;
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
