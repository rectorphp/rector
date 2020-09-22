<?php

declare(strict_types=1);

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;

$class = new FullyQualified('SomeClassName');

return new ClassConstFetch($class, 'SOME_CONSTANT');
