<?php

use PhpParser\Node\Expr\AssignOp\Mul;
use PhpParser\Node\Scalar\LNumber;

$left = new LNumber(5);
$right = new LNumber(10);

return new Mul($left, $right);
