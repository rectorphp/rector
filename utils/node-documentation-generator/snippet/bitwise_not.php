<?php

declare(strict_types=1);

use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new BitwiseNot($variable);
