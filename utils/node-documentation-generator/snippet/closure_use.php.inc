<?php

use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new ClosureUse($variable);
