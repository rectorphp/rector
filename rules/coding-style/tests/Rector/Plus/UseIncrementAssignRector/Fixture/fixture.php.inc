<?php

namespace Rector\CodingStyle\Tests\Rector\Plus\UseIncrementAssignRector\Fixture;

class SomeClass
{
    public function run()
    {
        $style = 0;

        $style += 1;

        $style -= 1;
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Plus\UseIncrementAssignRector\Fixture;

class SomeClass
{
    public function run()
    {
        $style = 0;

        ++$style;

        --$style;
    }
}

?>
