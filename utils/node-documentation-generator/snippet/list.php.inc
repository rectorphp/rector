<?php

use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');
$anotherVariable = new Variable('anoterVariableName');

return new List_([$variable, $anotherVariable]);
