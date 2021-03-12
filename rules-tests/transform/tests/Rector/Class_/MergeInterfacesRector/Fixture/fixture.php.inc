<?php

namespace Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass implements SomeInterface, SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass implements SomeInterface
{

}

?>
