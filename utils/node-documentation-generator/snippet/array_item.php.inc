<?php

use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

$value = new Variable('Tom');
$key = new String_('name');

return new ArrayItem($value, $key);
