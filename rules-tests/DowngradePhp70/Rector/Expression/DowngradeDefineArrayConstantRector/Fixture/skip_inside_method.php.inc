<?php

namespace Rector\Tests\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector\Fixture;

class SkipInsideMethod
{
    public function run()
    {
        define('ANIMALS', [
            'dog',
            'cat',
            'bird'
        ]);
    }
}

?>
