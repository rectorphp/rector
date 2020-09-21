<?php

use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new BitwiseNot($variable);
