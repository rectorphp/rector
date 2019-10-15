<?php

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class SomeClass
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

namespace Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector\Fixture;

class SomeClass
{
    public function run()
    {
        return 1;
    }
}

?>
