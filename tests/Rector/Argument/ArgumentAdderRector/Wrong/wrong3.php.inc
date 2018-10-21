<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Wrong;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\ContainerBuilder;

class CustomContainerBuilder extends ContainerBuilder
{
    public function addCompilerPass($pass, $type)
    {
    }
}
