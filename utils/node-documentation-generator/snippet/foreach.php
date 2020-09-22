<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;

$foreachedVariable = new Variable('foreachedVariableName');
$asVariable = new Variable('asVariable');

return new Foreach_($foreachedVariable, $asVariable);
