<?php

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Catch_;

$fullyQualified = new FullyQualified('ClassType');
$variable = new Variable('CaughtVariable');

return new Catch_([$fullyQualified], $variable);
