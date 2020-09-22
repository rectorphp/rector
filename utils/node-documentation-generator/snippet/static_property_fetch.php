<?php

declare(strict_types=1);

use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name\FullyQualified;

$class = new FullyQualified('StaticClassName');

return new StaticPropertyFetch($class, 'someProperty');
