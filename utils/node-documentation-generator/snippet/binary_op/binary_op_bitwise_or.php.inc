<?php

use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Scalar\LNumber;

$left = new LNumber(5);
$right = new LNumber(10);

return new BitwiseOr($left, $right);
