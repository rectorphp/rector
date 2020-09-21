<?php

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\UseUse;

$uses = [
    new UseUse('useUse')
];

return new GroupUse(new Name('prefix'), $uses);
