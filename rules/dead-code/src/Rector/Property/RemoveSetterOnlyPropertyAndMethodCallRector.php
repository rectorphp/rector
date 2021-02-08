<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Removing\NodeManipulator\ComplexNodeRemover;
use Rector\VendorLocker\VendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\RemoveSetterOnlyPropertyAndMethodCallRectorTest
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

    /**
     * @var PropertyFetchFinder
     */
    private $propertyFetchFinder;

    /**
     * @var ComplexNodeRemover
     */
    private $complexNodeRemover;

    public function __construct(
        PropertyManipulator $propertyManipulator,
        VendorLockResolver $vendorLockResolver,
        PropertyFetchFinder $propertyFetchFinder,
        ComplexNodeRemover $complexNodeRemover
    ) {
        $this->propertyManipulator = $propertyManipulator;
        $this->vendorLockResolver = $vendorLockResolver;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->complexNodeRemover = $complexNodeRemover;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes method that set values that are never used',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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

        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($node);
        $classMethodsToCheck = $this->collectClassMethodsToCheck($propertyFetches);

        $vendorLockedClassMethodNames = $this->getVendorLockedClassMethodNames($classMethodsToCheck);

        $this->complexNodeRemover->removePropertyAndUsages($node, $vendorLockedClassMethodNames);

        /** @var ClassMethod $method */
        foreach ($classMethodsToCheck as $method) {
            if (! $this->hasMethodSomeStmtsLeft($method)) {
                continue;
            }

            $classMethodName = $this->getName($method->name);
            if (in_array($classMethodName, $vendorLockedClassMethodNames, true)) {
                continue;
            }

            $this->complexNodeRemover->removeClassMethodAndUsages($method);
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

        /** @var Class_|Interface_|Trait_|null $classLike */
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return true;
        }

        if ($classLike instanceof Trait_) {
            return true;
        }

        if ($classLike instanceof Interface_) {
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
            if ($methodName === MethodName::CONSTRUCT) {
                continue;
            }

            $classMethod = $propertyFetch->getAttribute(AttributeKey::METHOD_NODE);
            if (! $classMethod instanceof ClassMethod) {
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
