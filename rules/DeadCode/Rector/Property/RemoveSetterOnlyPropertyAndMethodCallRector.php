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
use Rector\Core\Exception\ShouldNotHappenException;
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
 * @see \Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\RemoveSetterOnlyPropertyAndMethodCallRectorTest
 */
final class RemoveSetterOnlyPropertyAndMethodCallRector extends AbstractRector
{
    public function __construct(
        private PropertyManipulator $propertyManipulator,
        private VendorLockResolver $vendorLockResolver,
        private PropertyFetchFinder $propertyFetchFinder,
        private ComplexNodeRemover $complexNodeRemover
    ) {
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
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
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

        foreach ($classMethodsToCheck as $classMethodToCheck) {
            if (! $this->hasMethodSomeStmtsLeft($classMethodToCheck)) {
                continue;
            }

            $classMethodName = $this->getName($classMethodToCheck->name);
            if (in_array($classMethodName, $vendorLockedClassMethodNames, true)) {
                continue;
            }

            $this->complexNodeRemover->removeClassMethodAndUsages($classMethodToCheck);
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
            $classMethod = $propertyFetch->getAttribute(AttributeKey::METHOD_NODE);
            if (! $classMethod instanceof ClassMethod) {
                throw new ShouldNotHappenException();
            }

            // this rector does not remove empty constructors
            if ($this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
                continue;
            }

            $methodName = $this->getName($classMethod);
            $classMethodsToCheck[$methodName] = $classMethod;
        }

        return $classMethodsToCheck;
    }

    /**
     * @param array<string, ClassMethod> $methodsToCheck
     * @return string[]
     */
    private function getVendorLockedClassMethodNames(array $methodsToCheck): array
    {
        $vendorLockedClassMethodsNames = [];
        foreach ($methodsToCheck as $methodToCheck) {
            if (! $this->vendorLockResolver->isClassMethodRemovalVendorLocked($methodToCheck)) {
                continue;
            }

            $vendorLockedClassMethodsNames[] = $this->getName($methodToCheck);
        }

        return $vendorLockedClassMethodsNames;
    }

    private function hasMethodSomeStmtsLeft(ClassMethod $classMethod): bool
    {
        foreach ((array) $classMethod->stmts as $stmt) {
            if (! $this->nodesToRemoveCollector->isNodeRemoved($stmt)) {
                return false;
            }
        }

        return true;
    }
}
