<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

# ref https://3v4l.org/rgtjG
class SomeGotoDirect
{
    public function run($data)
    {
        if ($data === 1) {
            goto execute;
            execute:
                echo 'here';
                echo 'here 2';
        }
    }
}
