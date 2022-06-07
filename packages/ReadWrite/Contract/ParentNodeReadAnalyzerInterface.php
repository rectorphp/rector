<?php

declare (strict_types=1);
namespace Rector\ReadWrite\Contract;

use PhpParser\Node;
use PhpParser\Node\Expr;
interface ParentNodeReadAnalyzerInterface
{
    public function isRead(Expr $expr, Node $parentNode) : bool;
}
