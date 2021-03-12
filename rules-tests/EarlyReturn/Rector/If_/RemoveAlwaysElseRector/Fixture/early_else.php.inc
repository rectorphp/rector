<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class EarlyElse
{
    public function run($value)
    {
        if ($value) {
            return 5;
        } else {
            return 10;
        }
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class EarlyElse
{
    public function run($value)
    {
        if ($value) {
            return 5;
        }
        return 10;
    }
}

?>
