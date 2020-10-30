<?php

declare(strict_types=1);

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

$useUse = new UseUse(new Name('UsedNamespace'));

return new Use_([$useUse]);
