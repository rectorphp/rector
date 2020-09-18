<?php

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new PropertyFetch($variable, 'propertyName');
