<?php

namespace Rector\Arguments\Tests\Rector\ClassMethod\ArgumentDefaultValueReplacerRector\Fixture;

use Symfony\Component\DependencyInjection\ContainerBuilder;

function argumentDefalutValue()
{
    $container = new ContainerBuilder();
    $container->register('foo', 'stdClass')
        ->setScope(ContainerBuilder::SCOPE_PROTOTYPE);
}

?>
-----
<?php

namespace Rector\Arguments\Tests\Rector\ClassMethod\ArgumentDefaultValueReplacerRector\Fixture;

use Symfony\Component\DependencyInjection\ContainerBuilder;

function argumentDefalutValue()
{
    $container = new ContainerBuilder();
    $container->register('foo', 'stdClass')
        ->setScope(false);
}

?>
