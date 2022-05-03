<?php

namespace Rector\Tests\Php73\Rector\FuncCall\ArrayKeyFirstLastRector\Fixture;

class SkipBothResetAndKeyAssigned
{
    public function run($items)
    {
        $a = reset($items);
        $b = key($items);

        var_dump($a);
        var_dump($b);
    }
}
