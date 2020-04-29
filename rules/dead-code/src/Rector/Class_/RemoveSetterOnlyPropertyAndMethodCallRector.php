<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\PhpParser\Node\Manipulator\PropertyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\VendorLockResolver;

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

    /**
     * @var VendorLockResolver
     */
    private $vendorLockResolver;

    public function __construct(PropertyManipulator $propertyManipulator, VendorLockResolver $vendorLockResolver)
    {
        $this->propertyManipulator = $propertyManipulator;
        $this->vendorLockResolver = $vendorLockResolver;
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
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        $propertyFetches = $this->propertyManipulator->getAllPropertyFetch($node);
        $classMethodsToCheck = $this->collectClassMethodsToCheck($propertyFetches);

        $vendorLockedClassMethodNames = $this->getVendorLockedClassMethodNames($classMethodsToCheck);

        $this->removePropertyAndUsages($node, $vendorLockedClassMethodNames);

        /** @var ClassMethod $method */
        foreach ($classMethodsToCheck as $method) {
            if (! $this->hasMethodSomeStmtsLeft($method)) {
                continue;
            }

            $classMethodName = $this->getName($method->name);
            if (in_array($classMethodName, $vendorLockedClassMethodNames, true)) {
                continue;
            }

            $this->removeClassMethodAndUsages($method);
        }

        return $node;
    }

    private function shouldSkipProperty(Property $property): bool
    {
        if (count($property->props) !== 1) {
            return true;
        }

        if (! $property->isPrivate()) {
            return true;
        }

        /** @var Class_|Interface_|Trait_|null $classNode */
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return true;
        }

        if ($classNode instanceof Trait_) {
            return true;
        }

        if ($classNode instanceof Interface_) {
            return true;
        }

        return $this->propertyManipulator->isPropertyUsedInReadContext($property);
    }

    /**
     * @param PropertyFetch[]|StaticPropertyFetch[] $propertyFetches
     * @return ClassMethod[]
     */
    private function collectClassMethodsToCheck(array $propertyFetches): array
    {
        $classMethodsToCheck = [];

        foreach ($propertyFetches as $propertyFetch) {
            $methodName = $propertyFetch->getAttribute(AttributeKey::METHOD_NAME);
            // this rector does not remove empty constructors
            if ($methodName === '__construct') {
                continue;
            }

            /** @var ClassMethod|null $classMethod */
            $classMethod = $propertyFetch->getAttribute(AttributeKey::METHOD_NODE);
            if ($classMethod === null) {
                continue;
            }

            $classMethodsToCheck[$methodName] = $classMethod;
        }

        return $classMethodsToCheck;
    }

    /**
     * @param ClassMethod[] $methodsToCheck
     * @return string[]
     */
    private function getVendorLockedClassMethodNames(array $methodsToCheck): array
    {
        $vendorLockedClassMethodsNames = [];
        foreach ($methodsToCheck as $method) {
            if (! $this->vendorLockResolver->isClassMethodRemovalVendorLocked($method)) {
                continue;
            }

            $vendorLockedClassMethodsNames[] = $this->getName($method);
        }

        return $vendorLockedClassMethodsNames;
    }

    private function hasMethodSomeStmtsLeft(ClassMethod $classMethod): bool
    {
        foreach ((array) $classMethod->stmts as $stmt) {
            if (! $this->isNodeRemoved($stmt)) {
                return false;
            }
        }

        return true;
    }
}
