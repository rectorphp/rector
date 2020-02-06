<?php

namespace Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SomeClass
{
    use SomeTrait;
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SomeClass implements \Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface
{
    use SomeTrait;
}

?>
