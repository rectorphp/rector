<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDeadArrayKeyOverrideRector\Fixture;

class SomeClass
{
    public function run()
    {
        $values[1] = '1';
        $values[2] = '2';
        $values[1] = '3';
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDeadArrayKeyOverrideRector\Fixture;

class SomeClass
{
    public function run()
    {
        $values[1] = '3';
        $values[2] = '2';
    }
}

?>
