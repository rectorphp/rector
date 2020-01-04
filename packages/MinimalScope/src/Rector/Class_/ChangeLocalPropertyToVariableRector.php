<?php

declare(strict_types=1);

namespace Rector\MinimalScope\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\MinimalScope\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector\ChangeLocalPropertyToVariableRectorTest
 */
final class ChangeLocalPropertyToVariableRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change local property used in single method to local variable', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    private $count;
    public function run()
    {
        $this->count = 5;
        return $this->count;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $count = 5;
        return $count;
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isAnonymousClass($node)) {
            return null;
        }

        $privatePropertyNames = $this->classManipulator->getPrivatePropertyNames($node);
        $propertyUsageByMethods = $this->collectPropertyFetchByMethods($node, $privatePropertyNames);

        foreach ($propertyUsageByMethods as $propertyName => $methodNames) {
            if (count($methodNames) === 1) {
                continue;
            }

            unset($propertyUsageByMethods[$propertyName]);
        }

        $this->replacePropertyFetchesByLocalProperty($node, $propertyUsageByMethods);

        // remove properties
        foreach ($node->getProperties() as $property) {
            if (! $this->isNames($property, array_keys($propertyUsageByMethods))) {
                continue;
            }

            $this->removeNode($property);
        }

        return $node;
    }

    /**
     * @param string[][] $propertyUsageByMethods
     */
    private function replacePropertyFetchesByLocalProperty(Class_ $class, array $propertyUsageByMethods): void
    {
        foreach ($propertyUsageByMethods as $propertyName => $methodNames) {
            $methodName = $methodNames[0];
            $classMethod = $class->getMethod($methodName);
            if ($classMethod === null) {
                continue;
            }

            $this->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use (
                $propertyName
            ) {
                if (! $node instanceof PropertyFetch) {
                    return null;
                }

                if (! $this->isName($node, $propertyName)) {
                    return null;
                }

                return new Variable($propertyName);
            });
        }
    }

    /**
     * @param string[] $privatePropertyNames
     * @return string[][]
     */
    private function collectPropertyFetchByMethods(Class_ $class, array $privatePropertyNames): array
    {
        $propertyUsageByMethods = [];
        foreach ($privatePropertyNames as $privatePropertyName) {
            foreach ($class->getMethods() as $method) {
                $hasProperty = (bool) $this->betterNodeFinder->findFirst($method, function (Node $node) use (
                    $privatePropertyName
                ) {
                    if (! $node instanceof PropertyFetch) {
                        return false;
                    }

                    return (bool) $this->isName($node->name, $privatePropertyName);
                });

                if ($hasProperty === false) {
                    continue;
                }

                /** @var string $classMethodName */
                $classMethodName = $this->getName($method);
                $propertyUsageByMethods[$privatePropertyName][] = $classMethodName;
            }
        }
        return $propertyUsageByMethods;
    }
}
