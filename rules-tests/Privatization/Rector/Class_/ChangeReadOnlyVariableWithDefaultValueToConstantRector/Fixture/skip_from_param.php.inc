<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipFromParam
{
    public function run($a)
    {
        if ($a === 0) {
            $a = '0';
        }

        if ($a === 'a') {
            return 0;
        }

        if ($a === 'z') {
            return 10000;
        }

        return 0;
    }
}

?>
