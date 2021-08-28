<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel as BaseKernel;

class SomeClass
{
    public function run(?BaseKernel $baseKernel)
    {
        $anotherBaseKernel = $baseKernel ?? new BaseKernel();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel;

class SomeClass
{
    public function run(?AbstractKernel $baseKernel)
    {
        $anotherBaseKernel = $baseKernel ?? new AbstractKernel();
    }
}

?>
