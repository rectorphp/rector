<?php

namespace Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeInterface
{

}

?>
