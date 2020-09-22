<?php

declare(strict_types=1);

use PhpParser\Node\MatchArm;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

$conds = [new LNumber(1)];
$body = new String_('yes');

return new MatchArm($conds, $body);
