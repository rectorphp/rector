<?php

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\YieldFrom;

$variable = new Variable('variableName');

return new YieldFrom($variable);
