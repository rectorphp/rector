<?php

declare(strict_types=1);

use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;

$variable = new Variable('variableName');

$trueConstFetch = new ConstFetch(new Name('true'));
$falseConstFetch = new ConstFetch(new Name('false'));

return new Ternary($variable, $trueConstFetch, $falseConstFetch);
