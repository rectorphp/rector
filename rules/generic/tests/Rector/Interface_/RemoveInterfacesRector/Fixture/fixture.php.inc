<?php

namespace Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements SomeInterface, AnotherInterface
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements AnotherInterface
{
}

?>
