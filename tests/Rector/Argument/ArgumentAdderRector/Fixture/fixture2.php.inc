<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\ContainerBuilder;

class SomeClass2
{
    public function create()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addCompilerPass('pass', 'type');
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\ContainerBuilder;

class SomeClass2
{
    public function create()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addCompilerPass('pass', 'type', 0);
    }
}

?>
