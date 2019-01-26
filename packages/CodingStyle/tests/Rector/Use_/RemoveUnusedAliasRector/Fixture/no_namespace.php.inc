<?php

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel as BaseKernel;

class SomeClass extends BaseKernel
{
    public function run(BaseKernel $baseKernel)
    {
        $anotherBaseKernel = new BaseKernel();
    }
}

?>
-----
<?php

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel;

class SomeClass extends AbstractKernel
{
    public function run(AbstractKernel $baseKernel)
    {
        $anotherBaseKernel = new AbstractKernel();
    }
}

?>
