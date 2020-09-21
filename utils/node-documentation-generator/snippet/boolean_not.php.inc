<?php

use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('isEligible');

return new BooleanNot($variable);
