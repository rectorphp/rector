<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

$variable = new Variable('variableName');

$body = new String_('yes');
$cond = new LNumber(1);
$matchArm = new MatchArm([$cond], $body);

return new Match_($variable, [$matchArm]);
