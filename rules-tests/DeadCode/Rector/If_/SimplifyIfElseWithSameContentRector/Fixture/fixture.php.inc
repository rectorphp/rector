<?php

namespace Rector\Tests\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class Fixture
{
    public function run()
    {
        if (true) {
            return 1;
        } else {
            return 1;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class Fixture
{
    public function run()
    {
        return 1;
    }
}

?>
