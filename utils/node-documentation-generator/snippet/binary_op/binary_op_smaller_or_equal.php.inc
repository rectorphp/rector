<?php

use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Scalar\LNumber;

$left = new LNumber(5);
$right = new LNumber(10);

return new SmallerOrEqual($left, $right);
