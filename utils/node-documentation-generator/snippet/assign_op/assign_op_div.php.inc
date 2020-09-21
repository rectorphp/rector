<?php

use PhpParser\Node\Expr\AssignOp\Div;
use PhpParser\Node\Scalar\LNumber;

$left = new LNumber(5);
$right = new LNumber(10);

return new Div($left, $right);
