<?php

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

$variable = new Variable('variableName');
$value = new String_('some value');

return new Assign($variable, $value);
