<?php

use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new Clone_($variable);
