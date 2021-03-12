<?php

namespace Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

class SomeClass2
{
    public function create()
    {
        $containerBuilder = new SomeContainerBuilder();
        $containerBuilder->addCompilerPass('pass', 'type');
    }
}

?>
-----
<?php

namespace Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;

class SomeClass2
{
    public function create()
    {
        $containerBuilder = new SomeContainerBuilder();
        $containerBuilder->addCompilerPass('pass', 'type', 0);
    }
}

?>
