<?php

declare(strict_types=1);

use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new NullsafePropertyFetch($variable, 'someProperty');
