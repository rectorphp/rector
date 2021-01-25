<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel as BaseKernel;
use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\Another\AbstractKernel;

class SkipUsed extends BaseKernel
{
    public function run(BaseKernel $baseKernel, AbstractKernel $abstractKernel)
    {
        $anotherBaseKernel = new BaseKernel();
        $anotherAbstractKernel = new AbstractKernel();
    }
}
