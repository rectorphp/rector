<?php

declare(strict_types=1);

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\UseUse;

return new UseUse(new Name('UsedNamespace'));
