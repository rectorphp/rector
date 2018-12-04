<?php

namespace Rector\Tests\Rector\Interface_\MergeInterfacesRector\Wrong;

use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Tests\Rector\Interface_\MergeInterfacesRector\Wrong;

use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface
{

}

?>
