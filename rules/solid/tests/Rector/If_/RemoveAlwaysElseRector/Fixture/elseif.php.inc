<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

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

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

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
