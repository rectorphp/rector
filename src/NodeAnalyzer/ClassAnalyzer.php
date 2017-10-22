<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

final class ClassAnalyzer
{
    public function isAnonymousClassNode(Node $node): bool
    {
        return $node instanceof Class_ && $node->isAnonymous();
    }

    public function isNormalClass(Node $node): bool
    {
        return $node instanceof Class_ && ! $node->isAnonymous();
    }
}
