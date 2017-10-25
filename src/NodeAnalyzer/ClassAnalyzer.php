<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;

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

    /**
     * @param Class_|Interface_ $classLikeNode
     * @return string[]
     */
    public function resolveParentTypes(ClassLike $classLikeNode): array
    {
        $types = [];

        $parentClasses = (array) $classLikeNode->extends;
        foreach ($parentClasses as $parentClass) {
            /** @var FullyQualified $parentClass */
            $types[] = $parentClass->toString();
        }

        $interfaces = (array) $classLikeNode->implements;
        foreach ($interfaces as $interface) {
            /** @var FullyQualified $interface */
            $types[] = $interface->toString();
        }

        return $types;
    }
}
