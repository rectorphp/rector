<?php

use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new PreDec($variable);
