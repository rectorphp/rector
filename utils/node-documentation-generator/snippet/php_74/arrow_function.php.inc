<?php

use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Scalar\LNumber;

$subNodes['expr'] = new LNumber(1);

return new ArrowFunction($subNodes);
