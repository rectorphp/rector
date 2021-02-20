<?php

namespace Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

class SkipAlreadyAdded extends SomeContainerBuilder
{
    public function addCompilerPass($pass, $type, int $priority = 10)
    {
    }
}
