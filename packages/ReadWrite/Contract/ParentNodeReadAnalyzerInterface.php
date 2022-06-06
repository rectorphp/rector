<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\ReadWrite\Contract;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
interface ParentNodeReadAnalyzerInterface
{
    public function isRead(Expr $expr, Node $parentNode) : bool;
}
