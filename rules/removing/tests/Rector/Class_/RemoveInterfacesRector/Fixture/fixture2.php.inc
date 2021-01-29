<?php

namespace Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2 implements SomeInterface
{
}

?>
-----
<?php

namespace Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Fixture;

use Rector\Removing\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2
{
}

?>
