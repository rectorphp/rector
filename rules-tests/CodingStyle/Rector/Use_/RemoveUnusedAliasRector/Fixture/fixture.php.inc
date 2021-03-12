<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractInterface as BaseInterface;
use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel as BaseKernel;


class SomeClass extends BaseKernel implements BaseInterface
{
    public function run(BaseKernel $baseKernel): BaseInterface
    {
        $anotherBaseKernel = new BaseKernel();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractInterface;
use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel;


class SomeClass extends AbstractKernel implements AbstractInterface
{
    public function run(AbstractKernel $baseKernel): AbstractInterface
    {
        $anotherBaseKernel = new AbstractKernel();
    }
}

?>
