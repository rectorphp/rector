<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

class SomeClass
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

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

class SomeClass
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
