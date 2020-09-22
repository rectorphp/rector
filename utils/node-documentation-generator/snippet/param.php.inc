<?php

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;

$variable = new Variable('variableName');

return new Param($variable);
