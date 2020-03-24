<?php

namespace Rector\Php73\Tests\Rector\FuncCall\ArrayKeyFirstLastRector\Fixture;

class SkipOtherFunctionName
{
    public function run()
    {
        $a = [];
        ksort($a);
        key($a);
    }
}
