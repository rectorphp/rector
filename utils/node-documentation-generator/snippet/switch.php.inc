<?php

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Switch_;

$cond = new Variable('variableName');
$cases = [new Case_(new LNumber(1))];

return new Switch_($cond, $cases);
