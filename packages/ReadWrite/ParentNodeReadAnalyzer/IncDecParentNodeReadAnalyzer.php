<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ParentNodeReadAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface;
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
