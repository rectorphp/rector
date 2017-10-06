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
        /** @var FullyQualified $className */
        $classFullyQualifiedName = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);

        if ($classFullyQualifiedName instanceof FullyQualified) {
            return $classFullyQualifiedName->toString() === $className;
        }

        // e.g. "$form::FILLED"
        $nodeClassName = $classConstFetchNode->class->getAttribute(Attribute::CLASS_NAME);

        return $nodeClassName === $className;
    }

    /**
     * @param string[] $constantNames
     */
    private function isConstantName(ClassConstFetch $node, array $constantNames): bool
    {
        $nodeConstantName = $node->name->name;

        return in_array($nodeConstantName, $constantNames, true);
    }
}
