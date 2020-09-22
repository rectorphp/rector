<?php

declare(strict_types=1);

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new Arg($variable);
