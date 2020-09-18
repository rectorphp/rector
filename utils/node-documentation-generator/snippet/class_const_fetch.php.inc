<?php

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;

$class = new Name('SomeClassName');

return new ClassConstFetch($class, 'SOME_CONSTANT');
