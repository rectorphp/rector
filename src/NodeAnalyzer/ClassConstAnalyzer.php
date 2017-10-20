<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Node\Attribute;

final class ClassConstAnalyzer
{
    /**
     * @param string[] $constantNames
     */
    public function isTypeAndNames(Node $node, string $type, array $constantNames): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var ClassConstFetch $node */
        return $this->isNames($node, $constantNames);
    }

    public function matchTypes(Node $node, array $types): ?string
    {
        if (! $node instanceof ClassConstFetch) {
            return null;
        }

        $class = $this->resolveType($node);

        return in_array($class, $types, true) ? $class : null;
    }

    private function isType(Node $node, string $type): bool
    {
        if (! $node instanceof ClassConstFetch) {
            return false;
        }

        $nodeClass = $this->resolveType($node);

        return $nodeClass === $type;
    }

    /**
     * @param string[] $names
     */
    private function isNames(ClassConstFetch $node, array $names): bool
    {
        $nodeConstantName = $node->name->name;

        return in_array($nodeConstantName, $names, true);
    }

    private function resolveType(ClassConstFetch $classConstFetchNode): string
    {
        $classFullyQualifiedName = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);

        if ($classFullyQualifiedName instanceof FullyQualified) {
            return $classFullyQualifiedName->toString();
        }

        // e.g. "$form::FILLED"
        return (string) $classConstFetchNode->class->getAttribute(Attribute::CLASS_NAME);
    }
}
