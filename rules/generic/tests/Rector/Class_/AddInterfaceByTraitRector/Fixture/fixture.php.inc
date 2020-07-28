<?php

namespace Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SomeClass
{
    use SomeTrait;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SomeClass implements \Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface
{
    use SomeTrait;
}

?>
