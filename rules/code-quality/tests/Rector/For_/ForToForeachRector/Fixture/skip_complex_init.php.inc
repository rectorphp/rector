<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class SkipComplexInit
{
    public function run($tokens)
    {
        $o = new class {
            public function getResult()
            {
                return ['a'];
            }
        };

        for ($i = 0, $query = $o->getResult(), $c = count($query); $i < $c; $i ++)
        {
            echo $query[$i];
        }
    }
}

?>
