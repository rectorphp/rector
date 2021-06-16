<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\RemoveAlwaysElseRector\Fixture;

class ElseIf_
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

namespace Rector\Tests\EarlyReturn\Rector\If_\RemoveAlwaysElseRector\Fixture;

class ElseIf_
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
        }
        else {
            foo();
        }
    }
}

?>
