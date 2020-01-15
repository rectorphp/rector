<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\ElseIf_;

class SomeClass
{
    public function run()
    {
        if ($cond1) {
            return 'foo';
        } elseif ($cond2) {
            bar();
        } elseif ($cond3) {
            baz();
        } else {
            foo();
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\ElseIf_;

class SomeClass
{
    public function run()
    {
        if ($cond1) {
            return 'foo';
        }
        if ($cond2) {
            bar();
        } elseif ($cond3) {
            baz();
        } else {
            foo();
        }
    }
}

?>
