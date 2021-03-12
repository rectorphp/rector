<?php

namespace Rector\Tests\Php73\Rector\FuncCall\ArrayKeyFirstLastRector\Fixture;

class SkipOtherFunctionName
{
    public function run()
    {
        $a = [];
        ksort($a);
        key($a);
    }
}
