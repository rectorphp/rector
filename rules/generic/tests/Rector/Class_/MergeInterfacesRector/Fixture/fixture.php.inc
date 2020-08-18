<?php

namespace Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass implements SomeInterface, SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass implements SomeInterface
{

}

?>
