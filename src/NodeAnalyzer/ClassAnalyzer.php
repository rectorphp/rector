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
            $className = $this->resolveNameNode($classLikeNode);

            $types[] = $className;

            if ($classLikeNode->extends) {
                $types[] = class_parents($className);
            }
        }

        if ($this->isAnonymousClassNode($classLikeNode)) {
            /** @var FullyQualified $parentClass */
            $types[] = $this->resolveNameNode($classLikeNode->extends);
        }

        $interfaces = (array) $classLikeNode->implements;
        foreach ($interfaces as $interface) {
            /** @var FullyQualified $interface */
            $types[] = $interface->toString();
        }

        return $types;
    }

    /**
     * @param Name|ClassLike $node
     */
    private function resolveNameNode(Node $node): string
    {
        if ($node instanceof Name) {
            return $node->toString();
        }

        $nameNode = $node->getAttribute(Attribute::RESOLVED_NAME);
        if ($nameNode instanceof Name) {
            return $nameNode->toString();
        }

        return $node->name->toString();
    }
}
