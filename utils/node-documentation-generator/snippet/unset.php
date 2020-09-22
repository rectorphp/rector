<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Unset_;

$variable = new Variable('variableName');

return new Unset_([$variable]);
