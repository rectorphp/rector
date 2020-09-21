<?php

use PhpParser\Node\Expr\AssignOp\Minus;
use PhpParser\Node\Scalar\LNumber;

$left = new LNumber(5);
$right = new LNumber(10);

return new Minus($left, $right);
