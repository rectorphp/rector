<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\ContainerBuilder;

class AlreadyAdded extends ContainerBuilder
{
    public function addCompilerPass($pass, $type, int $priority = 10)
    {
    }
}
