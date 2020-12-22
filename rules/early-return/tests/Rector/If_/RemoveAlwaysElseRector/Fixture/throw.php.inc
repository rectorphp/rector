<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class SomeClass
{
    public function run()
    {
        if ($cond1) {
            throw new \Exception('should not happen');
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
            throw new \Exception('should not happen');
        }
        foo();
        return 'bar';
    }
}

?>
