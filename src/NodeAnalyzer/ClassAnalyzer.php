<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
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

    /**
     * @return string[]
     */
    public function resolveParentTypes(Class_ $classNode): array
    {
        $types = [];

        $parentClasses = (array) $classNode->extends;
        foreach ($parentClasses as $parentClass) {
            /** @var FullyQualified $parentClass */
            $types[] = $parentClass->toString();
        }

        $interfaces = (array) $classNode->implements;
        foreach ($interfaces as $interface) {
            /** @var FullyQualified $interface */
            $types[] = $interface->toString();
        }

        return $types;
    }
}
