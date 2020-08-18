<?php

namespace Rector\DeadCode\Tests\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector\Fixture;

class Nested
{
    public function run($value)
    {
        if ($value instanceof A && $value instanceof B) {
            if ($value instanceof B && $value instanceof A && $value instanceof A) {
                if ($value = 5) {
                    return $value instanceof B && $value instanceof A;
                }
            }
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector\Fixture;

class Nested
{
    public function run($value)
    {
        if ($value instanceof A && $value instanceof B) {
            if ($value instanceof B && $value instanceof A) {
                if ($value = 5) {
                    return $value instanceof B && $value instanceof A;
                }
            }
        }

        return false;
    }
}

?>
