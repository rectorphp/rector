<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Wrong;

class CustomContainerBuilder extends Symfony\Component\DependencyInjection\ContainerBuilder
{
    public function addCompilerPass($pass, $type, $priority = 0)
    {
    }
}
