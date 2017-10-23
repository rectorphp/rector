<?php declare (strict_types=1);

use PhpParser\Node\Stmt\UseUse;

array_map(function (UseUse $useUse) {
    return $useUse->getAlias()->toString();
}, null);
