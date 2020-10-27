<?php

namespace Rector\Nette\Tests\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector\Fixture;

use Nette\DI\ContainerBuilder;

class SomeClass
{
    public function run(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addDefinition('...')
            ->setClass('SeeSomeClass', [1, 2]);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector\Fixture;

use Nette\DI\ContainerBuilder;

class SomeClass
{
    public function run(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addDefinition('...')
            ->setFactory('SeeSomeClass', [1, 2]);
    }
}

?>
