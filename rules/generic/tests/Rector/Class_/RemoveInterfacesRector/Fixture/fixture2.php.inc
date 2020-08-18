<?php

namespace Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2 implements SomeInterface
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2
{
}

?>
