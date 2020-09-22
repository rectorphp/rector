<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new Isset_([$variable]);
