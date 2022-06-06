<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\ReadWrite\ParentNodeReadAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\PostDec;
use RectorPrefix20220606\PhpParser\Node\Expr\PostInc;
use RectorPrefix20220606\PhpParser\Node\Expr\PreDec;
use RectorPrefix20220606\PhpParser\Node\Expr\PreInc;
use RectorPrefix20220606\Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface;
final class IncDecParentNodeReadAnalyzer implements ParentNodeReadAnalyzerInterface
{
    public function isRead(Expr $expr, Node $parentNode) : bool
    {
        if ($parentNode instanceof PostDec) {
            return \true;
        }
        if ($parentNode instanceof PostInc) {
            return \true;
        }
        if ($parentNode instanceof PreInc) {
            return \true;
        }
        return $parentNode instanceof PreDec;
    }
}
