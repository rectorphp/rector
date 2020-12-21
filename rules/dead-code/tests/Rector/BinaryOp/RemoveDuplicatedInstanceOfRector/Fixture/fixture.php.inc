<?php

namespace Rector\DeadCode\Tests\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector\Fixture;

class Fixture
{
    public function run($value)
    {
        $isIt = $value instanceof A || $value instanceof A;
        $isIt = $value instanceof A && $value instanceof A;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector\Fixture;

class Fixture
{
    public function run($value)
    {
        $isIt = $value instanceof A;
        $isIt = $value instanceof A;
    }
}

?>
