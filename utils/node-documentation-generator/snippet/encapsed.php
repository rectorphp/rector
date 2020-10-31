<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Encapsed;

return new Encapsed([new Variable('variableName')]);
