<?php

declare(strict_types=1);

use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new NullsafeMethodCall($variable, 'methodName');
