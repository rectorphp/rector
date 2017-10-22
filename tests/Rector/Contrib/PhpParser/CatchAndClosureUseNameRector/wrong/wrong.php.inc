<?php declare (strict_types=1);

$closureUseNode = new PhpParser\Node\Expr\ClosureUse;
$string = $closureUseNode->var;

$string = $closureUseNode->var->name;
