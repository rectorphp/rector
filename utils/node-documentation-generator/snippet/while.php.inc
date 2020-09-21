<?php

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\While_;

return new While_(new Variable('variableName'));
