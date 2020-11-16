<?php

declare(strict_types=1);

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;

$thisVariable = new Variable('this');
$propertyFetch = new PropertyFetch($thisVariable, 'someProperty');

return new MethodCall($propertyFetch, 'methodName');
