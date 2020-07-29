<?php

namespace Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2 implements SomeInterface
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;

class SomeClass2
{
}

?>
