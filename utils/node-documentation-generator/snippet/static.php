<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;

$staticVars = [new StaticVar(new Variable('static'))];

return new Static_($staticVars);
