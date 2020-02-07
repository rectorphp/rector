<?php

namespace Rector\DeadCode\Tests\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector\Fixture;

class SomeClass
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

namespace Rector\DeadCode\Tests\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector\Fixture;

class SomeClass
{
    public function run($value)
    {
        $isIt = $value instanceof A;
        $isIt = $value instanceof A;
    }
}

?>
