<?php

declare(strict_types=1);

use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');

return new PostInc($variable);
