<?php

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\StaticVar;

$variable = new Variable('variableName');

return new StaticVar($variable);
