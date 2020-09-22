<?php

declare(strict_types=1);

use PhpParser\Node\Expr\BinaryOp\BitwiseAnd;
use PhpParser\Node\Scalar\LNumber;

$left = new LNumber(5);
$right = new LNumber(10);

return new BitwiseAnd($left, $right);
