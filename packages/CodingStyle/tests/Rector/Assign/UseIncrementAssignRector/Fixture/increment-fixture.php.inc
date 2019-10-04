<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\UseIncrementAssignRector\Fixture;

class SomeClass
{
    public function run()
    {
        $style = 0;

        $style += 1;
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\UseIncrementAssignRector\Fixture;

class SomeClass
{
    public function run()
    {
        $style = 0;

        ++$style;
    }
}

?>
