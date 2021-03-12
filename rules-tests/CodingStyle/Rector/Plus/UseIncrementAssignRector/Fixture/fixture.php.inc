<?php

namespace Rector\Tests\CodingStyle\Rector\Plus\UseIncrementAssignRector\Fixture;

class Fixture
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

namespace Rector\Tests\CodingStyle\Rector\Plus\UseIncrementAssignRector\Fixture;

class Fixture
{
    public function run()
    {
        $style = 0;

        ++$style;

        --$style;
    }
}

?>
