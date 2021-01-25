<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

class SkipAlreadyAdded extends SomeContainerBuilder
{
    public function addCompilerPass($pass, $type, int $priority = 10)
    {
    }
}
