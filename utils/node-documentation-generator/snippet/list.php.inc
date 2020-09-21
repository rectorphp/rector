<?php

use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new List_([$variable]);
