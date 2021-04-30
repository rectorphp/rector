<?php

namespace Rector\Tests\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector\Fixture;

class SkipNotDefine
{
    public function run()
    {
        echo strlen('test');
    }
}

?>
