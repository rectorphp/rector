<?php

namespace Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements SomeInterface, AnotherInterface
{
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements AnotherInterface
{
}

?>
