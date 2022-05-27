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
final class IncDecParentNodeReadAnalyzer implements \Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface
{
    public function isRead(\PhpParser\Node\Expr $expr, \PhpParser\Node $parentNode) : bool
    {
        if ($parentNode instanceof \PhpParser\Node\Expr\PostDec) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node\Expr\PostInc) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node\Expr\PreInc) {
            return \true;
        }
        return $parentNode instanceof \PhpParser\Node\Expr\PreDec;
    }
}
