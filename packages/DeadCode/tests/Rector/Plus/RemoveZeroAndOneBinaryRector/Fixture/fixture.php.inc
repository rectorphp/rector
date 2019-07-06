<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveZeroAndOneBinaryRector\Fixture;

class SomeClass
{
    public function run()
    {
        $value = 5 * 1;
        $value = 5 / 1;
        $value = 5 + 0;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveZeroAndOneBinaryRector\Fixture;

class SomeClass
{
    public function run()
    {
        $value = 5;
        $value = 5;
        $value = 5;
    }
}

?>
