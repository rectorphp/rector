<?php

namespace Rector\Generic\Tests\Rector\Interface_\MergeInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Interface_\MergeInterfacesRector\Fixture;

use Rector\Generic\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements Rector\Generic\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface
{

}

?>
