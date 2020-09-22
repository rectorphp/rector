<?php

declare(strict_types=1);

use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new ErrorSuppress($variable);
