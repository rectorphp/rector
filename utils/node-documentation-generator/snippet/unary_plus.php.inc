<?php

use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new UnaryPlus($variable);
