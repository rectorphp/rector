<?php

namespace Rector\Tests\Rector\Interface_\MergeInterfacesRector\Fixture;

use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Tests\Rector\Interface_\MergeInterfacesRector\Fixture;

use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface
{

}

?>
