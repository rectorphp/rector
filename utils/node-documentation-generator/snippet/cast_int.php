<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Variable;

$expr = new Variable('variableName');

return new Int_($expr);
