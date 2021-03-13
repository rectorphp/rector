<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements SomeInterface, AnotherInterface
{
}

?>
-----
<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;
use Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Source\AnotherInterface;

class SomeClass implements AnotherInterface
{
}

?>
