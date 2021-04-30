<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDefineArrayConstantRector\Fixture;

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
