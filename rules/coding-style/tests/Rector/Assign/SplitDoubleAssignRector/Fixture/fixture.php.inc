<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\SplitDoubleAssignRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Assign\SplitDoubleAssignRector\Fixture;

class Fixture
{
    public function run()
    {
        $one = 1;
        $two = 1;
    }
}

?>
