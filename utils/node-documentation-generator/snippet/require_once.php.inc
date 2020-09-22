<?php

use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new Include_($variable, Include_::TYPE_REQUIRE_ONCE);
