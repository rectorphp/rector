<?php

use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;

$name = new Name('true');

return new ConstFetch($name);
