<?php

namespace Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Fixture;

use Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass implements SomeInterface, SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Fixture;

use Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass implements SomeInterface
{

}

?>
