<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

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