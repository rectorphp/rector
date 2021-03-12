<?php

namespace Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

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

namespace Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

class SomeClass
{
    public function create()
    {
        $containerBuilder = new SomeContainerBuilder();
        $containerBuilder->compile(false);
    }
}

?>
