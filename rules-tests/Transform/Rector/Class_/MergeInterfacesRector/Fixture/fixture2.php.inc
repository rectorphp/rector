<?php

namespace Rector\Tests\Transform\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements Rector\Tests\Transform\Rector\Class_\MergeInterfacesRector\Source\SomeInterface
{

}

?>
