<?php

namespace Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Fixture;

use Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements SomeOldInterface
{

}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Fixture;

use Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

class SomeClass2 implements Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface
{

}

?>
