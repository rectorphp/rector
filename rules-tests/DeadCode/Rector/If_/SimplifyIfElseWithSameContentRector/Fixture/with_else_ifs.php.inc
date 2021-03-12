<?php

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class WithElseIfs
{
    public function run()
    {
        if (true) {
            return 1;
        } elseif (false) {
            return 1;
        } else {
            return 1;
        }
    }

    public function go()
    {
        if (true) {
            return 1;
        } elseif (false) {
            return 1;
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class WithElseIfs
{
    public function run()
    {
        return 1;
    }

    public function go()
    {
        if (true) {
            return 1;
        } elseif (false) {
            return 1;
        }
    }
}

?>
