<?php

namespace Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements Rector\Transform\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeInterface
{

}

?>
