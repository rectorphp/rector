<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDefineArrayConstantRector\Fixture;

class SkipNotDefine
{
    public function run()
    {
        echo strlen('test');
    }
}

?>
