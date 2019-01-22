<?php

namespace Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements SomeInterface, AnotherInterface
{
}

?>
-----
<?php

namespace Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements AnotherInterface
{
}

?>
