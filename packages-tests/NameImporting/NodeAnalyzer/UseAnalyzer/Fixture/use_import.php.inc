<?php

declare(strict_types=1);

namespace Rector\Tests\NameImporting\NodeAnalyzer\UseAnalyzer\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractInterface as BaseInterface;
use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel as BaseKernel;

class SomeClass extends BaseKernel implements BaseInterface
{
    public function run(BaseKernel $baseKernel): BaseInterface
    {
        $anotherBaseKernel = new BaseKernel();
    }
}
