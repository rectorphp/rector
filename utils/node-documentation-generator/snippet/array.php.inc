<?php

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

$value = new Variable('Tom');
$key = new String_('name');

$arrayItem = new ArrayItem($value, $key);

return new Array_([$arrayItem]);
