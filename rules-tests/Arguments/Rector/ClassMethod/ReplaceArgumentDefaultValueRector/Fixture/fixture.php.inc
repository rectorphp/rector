<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Fixture;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Fixture {
    function argumentDefalutValue()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')
            ->setScope(ContainerBuilder::SCOPE_PROTOTYPE);
    }
}

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Fixture;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Fixture {
    function argumentDefalutValue()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')
            ->setScope(false);
    }
}

?>
