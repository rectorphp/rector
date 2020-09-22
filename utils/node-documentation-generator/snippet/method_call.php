<?php

declare(strict_types=1);

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('someObject');

return new MethodCall($variable, 'methodName');
