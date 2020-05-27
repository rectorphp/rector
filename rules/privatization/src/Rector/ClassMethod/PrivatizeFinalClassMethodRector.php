<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\PrivatizeFinalClassMethodRectorTest
 */
final class PrivatizeFinalClassMethodRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change protected class method to private if possible', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    protected function someMethod()
    {
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    private function someMethod()
    {
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        if (! $class->isFinal()) {
            return null;
        }

        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        if ($class->extends === null) {
            $this->makePrivate($node);
            return $node;
        }

        if ($this->isClassMethodVisibilityGuardedByParent($node, $class)) {
            return null;
        }

        $this->makePrivate($node);

        return $node;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($this->isName($classMethod, 'createComponent*')) {
            return true;
        }

        return ! $classMethod->isProtected();
    }

    private function isClassMethodVisibilityGuardedByParent(ClassMethod $classMethod, Class_ $class): bool
    {
        if ($class->extends === null) {
            return false;
        }

        $parentClasses = $this->getParentClasses($class);
        $propertyName = $this->getName($classMethod);

        foreach ($parentClasses as $parentClass) {
            if (method_exists($parentClass, $propertyName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getParentClasses(Class_ $class): array
    {
        /** @var string $className */
        $className = $this->getName($class);

        return class_parents($className);
    }
}
