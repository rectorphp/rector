<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\PropertyManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\RemoveSetterOnlyPropertyAndMethodCallRectorTest
 */
final class RemoveSetterOnlyPropertyAndMethodCallRector extends AbstractRector
{
    /**
     * @var PropertyManipulator
     */
    private $propertyManipulator;

    public function __construct(PropertyManipulator $propertyManipulator)
    {
        $this->propertyManipulator = $propertyManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes method that set values that are never used', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    private $name;

    public function setName($name)
    {
        $this->name = $name;
    }
}

class ActiveOnlySetter
{
    public function run()
    {
        $someClass = new SomeClass();
        $someClass->setName('Tom');
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
}

class ActiveOnlySetter
{
    public function run()
    {
        $someClass = new SomeClass();
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
        return [PropertyProperty::class];
    }

    /**
     * @param PropertyProperty $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        if ($this->propertyManipulator->isPropertyUsedInReadContext($node)) {
            return null;
        }

        $propertyFetches = $this->propertyManipulator->getAllPropertyFetch($node);

        $methodsToCheck = [];
        foreach ($propertyFetches as $propertyFetch) {
            $methodName = $propertyFetch->getAttribute(AttributeKey::METHOD_NAME);
            if ($methodName !== '__construct') {
                //this rector does not remove empty constructors
                $methodsToCheck[$methodName] =
                    $propertyFetch->getAttribute(AttributeKey::METHOD_NODE);
            }
        }

        $this->removePropertyAndUsages($node);

        /** @var ClassMethod $method */
        foreach ($methodsToCheck as $method) {
            if ($this->methodHasNoStmtsLeft($method)) {
                $this->removeClassMethodAndUsages($method);
            }
        }

        return $node;
    }

    protected function methodHasNoStmtsLeft(ClassMethod $classMethod): bool
    {
        foreach ((array) $classMethod->stmts as $stmt) {
            if (! $this->isNodeRemoved($stmt)) {
                return false;
            }
        }
        return true;
    }

    private function shouldSkipProperty(PropertyProperty $propertyProperty): bool
    {
        if (! $this->propertyManipulator->isPrivate($propertyProperty)) {
            return true;
        }

        /** @var Class_|Interface_|Trait_|null $classNode */
        $classNode = $propertyProperty->getAttribute(AttributeKey::CLASS_NODE);
        return $classNode === null || $classNode instanceof Trait_ || $classNode instanceof Interface_;
    }
}
