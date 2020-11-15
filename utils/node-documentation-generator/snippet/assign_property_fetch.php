<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

$propertyFetch = new PropertyFetch(new Variable('someObject'), 'someProperty');
$value = new String_('some value');

return new Assign($propertyFetch, $value);
