<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeContainerBuilder;

class AlreadyAdded extends SomeContainerBuilder
{
    public function addCompilerPass($pass, $type, int $priority = 10)
    {
    }
}
