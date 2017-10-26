<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\Node\Attribute;

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
    public function resolveTypeAndParentTypes(ClassLike $classLikeNode): array
    {
        $types = [];

        if (! $this->isAnonymousClassNode($classLikeNode)) {
            $types[] = $this->resolveNameNode($classLikeNode);
        }

        $currentClassNode = $classLikeNode;
        while ($currentClassNode->extends) {
            /** @var FullyQualified $parentClass */
            $types[] = $this->resolveNameNode($classLikeNode->extends);

            $currentClassNode = $currentClassNode->extends;
        }

        $interfaces = (array) $classLikeNode->implements;
        foreach ($interfaces as $interface) {
            /** @var FullyQualified $interface */
            $types[] = $interface->toString();
        }

        return $types;
    }

    private function resolveNameNode(ClassLike $classLikeNode): string
    {
        $nameNode = $classLikeNode->getAttribute(Attribute::RESOLVED_NAME);
        if ($nameNode instanceof Name) {
            return $nameNode->toString();
        }

        return $classLikeNode->name->toString();
    }
}
