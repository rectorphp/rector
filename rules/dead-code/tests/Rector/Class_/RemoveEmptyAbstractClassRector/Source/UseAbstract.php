<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Source;

use Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\FixtureExtraFiles\SkipUsedAbstractClass;

final class UseAbstract
{
    public function __construct(?SkipUsedAbstractClass $class = null)
    {
    }
}
