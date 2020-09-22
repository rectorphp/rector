<?php

use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new UnaryMinus($variable);
