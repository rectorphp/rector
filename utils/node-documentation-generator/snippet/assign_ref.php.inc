<?php

use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');
$variableRef = new Variable('someOtherVariable');

return new AssignRef($variable, $variableRef);
