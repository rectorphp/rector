<?php

namespace Rector\Tests\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector\Fixture;

class Fixture
{
    public function run()
    {
        $isTrue = true && 5 === 1;

        $isFalse = true && true && 5 === 1;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector\Fixture;

class Fixture
{
    public function run()
    {
        $isTrue = 5 === 1;

        $isFalse = 5 === 1;
    }
}

?>
