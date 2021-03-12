<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Source;

abstract class AbstractClassRequiringAbstractMethod
{
    protected abstract function setName(string $name);
}
