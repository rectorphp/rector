<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

# ref https://3v4l.org/nmvEh
class SomeGotoIndirect
{
    public function run($data)
    {
        if ($data === 1) {
            goto execute;
            echo 'test';
        }

        execute:
            echo 'here';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

# ref https://3v4l.org/nmvEh
class SomeGotoIndirect
{
    public function run($data)
    {
        if ($data === 1) {
            goto execute;
        }

        execute:
            echo 'here';
    }
}

?>
