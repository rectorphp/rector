<?php

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Global_;

$variable = new Variable('globalVariable');

return new Global_([$variable]);
