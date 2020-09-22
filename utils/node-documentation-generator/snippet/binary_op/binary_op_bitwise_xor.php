<?php

declare(strict_types=1);

use PhpParser\Node\Expr\BinaryOp\BitwiseXor;
use PhpParser\Node\Scalar\LNumber;

$left = new LNumber(5);
$right = new LNumber(10);

return new BitwiseXor($left, $right);
