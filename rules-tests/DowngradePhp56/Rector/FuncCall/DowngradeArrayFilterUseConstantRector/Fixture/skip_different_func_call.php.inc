<?php

namespace Rector\Tests\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector\Fixture;

final class SkipDifferentFuncCall
{
    public function run()
    {
        strlen('test');
    }
}
