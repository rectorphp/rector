<?php

declare(strict_types=1);

use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Scalar\LNumber;

$left = new LNumber(5);
$right = new LNumber(10);

return new GreaterOrEqual($left, $right);
