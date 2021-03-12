<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Source;

use Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\FixtureExtraFiles\SkipUsedAbstractClass;

final class UseAbstract
{
    public function __construct(?SkipUsedAbstractClass $class = null)
    {
    }
}
