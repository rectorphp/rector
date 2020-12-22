<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class SomeClass
{
    public function run()
    {
        if ($cond1) {
            exit('bye');
        } else {
            foo();
            return 'bar';
        }
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class SomeClass
{
    public function run()
    {
        if ($cond1) {
            exit('bye');
        }
        foo();
        return 'bar';
    }
}

?>
