<?php

namespace Rector\Tests\CodingStyle\Rector\Assign\SplitDoubleAssignRector\Fixture;

class Fixture
{
    public function run()
    {
        $one = $two = 1;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Assign\SplitDoubleAssignRector\Fixture;

class Fixture
{
    public function run()
    {
        $one = 1;
        $two = 1;
    }
}

?>
