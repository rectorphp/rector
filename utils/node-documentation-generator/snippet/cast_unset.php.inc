<?php

use PhpParser\Node\Expr\Cast\Unset_;
use PhpParser\Node\Expr\Variable;

$expr = new Variable('variableName');

return new Unset_($expr);
