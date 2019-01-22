<?php

namespace Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2 implements SomeInterface
{
}

?>
-----
<?php

namespace Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2
{
}

?>
