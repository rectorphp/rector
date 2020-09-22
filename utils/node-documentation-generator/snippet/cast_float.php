<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Variable;

$expr = new Variable('variableName');

return new Double($expr);
