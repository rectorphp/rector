<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeContainerBuilder;

class SomeClass
{
    public function create()
    {
        $containerBuilder = new SomeContainerBuilder();
        $containerBuilder->compile();
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeContainerBuilder;

class SomeClass
{
    public function create()
    {
        $containerBuilder = new SomeContainerBuilder();
        $containerBuilder->compile(false);
    }
}

?>
