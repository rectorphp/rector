<?php

namespace Rector\DeadCode\Tests\Rector\BooleanAnd\RemoveAndTrueRector\Fixture;

class Something
{
    public function run()
    {
        $isTrue = true && true;

        $isTrue = true && true && 1 === 14;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\BooleanAnd\RemoveAndTrueRector\Fixture;

class Something
{
    public function run()
    {
        $isTrue = true;

        $isTrue = 1 === 14;
    }
}

?>
