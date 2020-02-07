<?php

namespace Rector\DeadCode\Tests\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector\Fixture;

class JumpAroundOne
{
    public function run($value)
    {
        $isIt = $value instanceof A && $value instanceof B && $value instanceof A;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector\Fixture;

class JumpAroundOne
{
    public function run($value)
    {
        $isIt = $value instanceof A && $value instanceof B;
    }
}

?>
