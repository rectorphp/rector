<?php

declare(strict_types=1);

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;

$fullyQualified = new FullyQualified('ClassName');

return new StaticCall($fullyQualified, 'methodName');
