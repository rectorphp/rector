<?php

namespace Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements SomeInterface, AnotherInterface
{
}

?>
-----
<?php

namespace Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements AnotherInterface
{
}

?>
