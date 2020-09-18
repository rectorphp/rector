<?php

use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new ErrorSuppress($variable);
