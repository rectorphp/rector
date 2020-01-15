<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\SimpleIf;

class SomeClass
{
    public function run()
    {
        if ($cond1) {
            return 'foo';
        }
    }
}

?>
