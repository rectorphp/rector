<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class Fixture
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        } else {
            return 10;
        }
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class Fixture
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        }
        return 10;
    }
}

?>
