<?php

use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;

$variable = new Variable('variableName');
$class = new FullyQualified('SomeClassName');

return new Instanceof_($variable, $class);
