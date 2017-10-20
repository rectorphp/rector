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
    public function isClassConstFetchOfClassAndConstantNames(Node $node, string $class, array $constantNames): bool
    {
        if (! $node instanceof ClassConstFetch) {
            return false;
        }

        if (! $this->isClassName($node, $class)) {
            return false;
        }

        return $this->isConstantName($node, $constantNames);
    }

    private function isClassName(ClassConstFetch $classConstFetchNode, string $className): bool
    {
        $nodeClass = $this->resolveClass($classConstFetchNode);

        return $nodeClass === $className;
    }

    /**
     * @param string[] $constantNames
     */
    private function isConstantName(ClassConstFetch $node, array $constantNames): bool
    {
        $nodeConstantName = $node->name->name;

        return in_array($nodeConstantName, $constantNames, true);
    }

    public function matchTypes(Node $node, array $types): ?string
    {
        if (! $node instanceof ClassConstFetch) {
            return null;
        }

        $class = $this->resolveClass($node);

        return in_array($class, $types, true) ? $class : null;
    }

    private function resolveClass(ClassConstFetch $classConstFetchNode): string
    {
        $classFullyQualifiedName = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);

        if ($classFullyQualifiedName instanceof FullyQualified) {
            return $classFullyQualifiedName->toString();
        }

        // e.g. "$form::FILLED"
        return (string) $classConstFetchNode->class->getAttribute(Attribute::CLASS_NAME);
    }
}
