<?php

namespace Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SomeClass
{
    use SomeTrait;
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SomeClass implements \Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface
{
    use SomeTrait;
}

?>
