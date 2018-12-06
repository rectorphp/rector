<?php

namespace Rector\Tests\Rector\Interface_\MergeInterfacesRector\Wrong;

use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass implements SomeInterface, SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Tests\Rector\Interface_\MergeInterfacesRector\Wrong;

use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass implements SomeInterface
{

}

?>
