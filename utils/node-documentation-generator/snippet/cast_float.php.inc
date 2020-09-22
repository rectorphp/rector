<?php

use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Variable;

$expr = new Variable('variableName');

return new Double($expr);
