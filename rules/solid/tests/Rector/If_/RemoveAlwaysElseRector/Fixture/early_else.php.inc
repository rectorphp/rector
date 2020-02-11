<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

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

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

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
