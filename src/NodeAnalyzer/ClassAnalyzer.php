<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
final class ClassAnalyzer
{
    public function isAnonymousClass(Node $node) : bool
    {
        if ($node instanceof New_) {
            return $this->isAnonymousClass($node->class);
        }
        if ($node instanceof Class_) {
            return $node->isAnonymous();
        }
        return \false;
    }
}
