<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

calledFunction();
return;

function calledFunction()
{
    define("SOMETHING",true);
}
