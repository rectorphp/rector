<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2 implements SomeInterface
{
}

?>
-----
<?php

namespace Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2
{
}

?>
