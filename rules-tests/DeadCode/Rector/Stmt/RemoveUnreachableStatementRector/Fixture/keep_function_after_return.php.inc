<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

calledFunction();
return;

function calledFunction()
{
    define("SOMETHING",true);
}
