<?php

namespace Rector\Php\Tests\Rector\BinaryOp\IsCountableRector\Fixture;

function isCountable()
{
    is_array($foo) || $foo instanceof Countable;
}

?>
