<?php

declare(strict_types=1);

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

$variable = new Variable('someObject');

$args = [];
$args[] = new Arg(new String_('yes'));
$args[] = new Arg(new String_('maybe'));

return new MethodCall($variable, 'methodName', $args);
