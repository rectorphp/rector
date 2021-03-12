<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Source\AbstractClassRequiringAbstractMethod;

class SkipAbstractClassRequired extends AbstractClassRequiringAbstractMethod
{
    private $name;
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
