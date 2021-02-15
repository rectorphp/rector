<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AbstractClassMulti
{
}

abstract class SkipAbstractClassUsedMulti extends AbstractClassMulti
{
}

final class AnyClass2
{
	public function process($some)
    {
        if ($some instanceof SkipAbstractClassUsedMulti) {

        }
    }
}