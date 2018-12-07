<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\ContainerBuilder;

class SomeClass
{
    public function create()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->compile();
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\ContainerBuilder;

class SomeClass
{
    public function create()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->compile(false);
    }
}

?>
