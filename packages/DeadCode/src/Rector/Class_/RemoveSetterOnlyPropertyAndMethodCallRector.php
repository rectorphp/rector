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

        $propertyFetches = $this->propertyManipulator->getAllPropertyFetch($node);

        /** @var ClassMethod[] $methodsToCheck */
        $methodsToCheck = [];
        foreach ($propertyFetches as $propertyFetch) {
            $methodName = $propertyFetch->getAttribute(AttributeKey::METHOD_NAME);
            if ($methodName !== '__construct') {
                //this rector does not remove empty constructors
                $methodsToCheck[$methodName] = $propertyFetch->getAttribute(AttributeKey::METHOD_NODE);
            }
        }

        $vendorLockedClassMethodNames = $this->getVendorLockedClassMethodNames($methodsToCheck);
        $this->removePropertyAndUsages($node, $vendorLockedClassMethodNames);

        /** @var ClassMethod $method */
        foreach ($methodsToCheck as $method) {
            if (! $this->methodHasNoStmtsLeft($method)) {
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
        if ($classNode === null) {
            return true;
        }

        if ($classNode instanceof Trait_) {
            return true;
        }

        if ($classNode instanceof Interface_) {
            return true;
        }

        if ($this->propertyManipulator->isPropertyUsedInReadContext($propertyProperty)) {
            return true;
        }

        return false;
    }

    private function isClassMethodRemovalVendorLocked(ClassMethod $classMethod): bool
    {
        $classMethodName = $this->getName($classMethod);

        /** @var Class_|null $class */
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return false;
        }

        // required by interface?
        foreach ($class->implements as $implement) {
            $implementedInterfaceName = $this->getName($implement);

            if (interface_exists($implementedInterfaceName)) {
                $interfaceMethods = get_class_methods($implementedInterfaceName);
                if (in_array($classMethodName, $interfaceMethods, true)) {
                    return true;
                }
            }
        }

        // required by abstract class?
        // @todo

        return false;
    }

    /**
     * @param ClassMethod[] $methodsToCheck
     * @return string[]
     */
    private function getVendorLockedClassMethodNames(array $methodsToCheck): array
    {
        $vendorLockedClassMethodsNames = [];
        foreach ($methodsToCheck as $method) {
            if (! $this->isClassMethodRemovalVendorLocked($method)) {
                continue;
            }

            $vendorLockedClassMethodsNames[] = $this->getName($method);
        }

        return $vendorLockedClassMethodsNames;
    }
}
