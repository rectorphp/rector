<?php

declare(strict_types=1);

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;

$args = [new Arg(new Variable('someVariable'))];

return new FuncCall(new Name('func_call'), $args);
