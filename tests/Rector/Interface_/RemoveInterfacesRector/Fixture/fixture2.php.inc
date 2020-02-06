<?php

namespace Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2 implements SomeInterface
{
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2
{
}

?>
