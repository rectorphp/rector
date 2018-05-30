<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

/**
 * Read-only utils for ClassLike|Class_|Trait_|Interface_ Node:
 * "class" SomeClass, "interface" Interface, "trait" Trait
 */
final class ClassLikeAnalyzer
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
