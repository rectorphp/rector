<?php

declare(strict_types=1);

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;

$variable = new Variable('variableName');
$dimension = new LNumber(0);

return new ArrayDimFetch($variable, $dimension);
