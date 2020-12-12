<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

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

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

class SomeClass
{
    public function create()
    {
        $containerBuilder = new SomeContainerBuilder();
        $containerBuilder->compile(false);
    }
}

?>
