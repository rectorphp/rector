<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

if (mt_rand(0, 1000)) {
    $skipClassUnder = new SkipClassUnder();
    return 1;
}

return 2;

// it can be still autoloaded and used abvoe
class SkipClassUnder
{
}
