<?php

declare(strict_types=1);

use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;

$variable = new Variable('variableName');
$anotherVariable = new Variable('anoterVariableName');

$arrayItems = [new ArrayItem($variable), new ArrayItem($anotherVariable)];

return new List_($arrayItems);
