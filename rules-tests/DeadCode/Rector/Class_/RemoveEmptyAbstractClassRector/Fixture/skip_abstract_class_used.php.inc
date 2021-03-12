<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AbstractClassUsed
{
}

class skipAbstractClassUsed extends AbstractClassUsed
{
}

final class AnyClass
{
	public function process($some)
    {
        if ($some instanceof AbstractClassUsed) {

        }
    }
}
