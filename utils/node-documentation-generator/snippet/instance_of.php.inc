<?php

use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;

$variable = new Variable('variableName');
$class = new Name('SomeClassName');

return new Instanceof_($variable, $class);
