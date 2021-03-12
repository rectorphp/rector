<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel as BaseKernel;
use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\Another\AbstractKernel;

class SkipUsed extends BaseKernel
{
    public function run(BaseKernel $baseKernel, AbstractKernel $abstractKernel)
    {
        $anotherBaseKernel = new BaseKernel();
        $anotherAbstractKernel = new AbstractKernel();
    }
}
