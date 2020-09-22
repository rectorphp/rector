<?php

declare(strict_types=1);

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new PropertyFetch($variable, 'propertyName');
