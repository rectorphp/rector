<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

class SkipAlreadyAdded extends SomeContainerBuilder
{
    public function addCompilerPass($pass, $type, int $priority = 10)
    {
    }
}
