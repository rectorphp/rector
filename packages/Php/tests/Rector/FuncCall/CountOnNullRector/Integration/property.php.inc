<?php

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Integration;

use PhpParser\Node\Expr\Isset_;

$issetNode = new Isset_([]);

if (! isset($issetNode->vars[0]) || count($issetNode->vars) > 1) {
}
