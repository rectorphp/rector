<?php

namespace Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements SomeInterface, AnotherInterface
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements AnotherInterface
{
}

?>
