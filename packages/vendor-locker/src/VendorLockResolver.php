<?php

declare(strict_types=1);

namespace Rector\VendorLocker;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

/**
 * @todo decouple to standalone package "packages/vendor-locker"
 */
final class VendorLockResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    /**
     * @var ReturnNodeVendorLockResolver
     */
    private $returnNodeVendorLockResolver;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ParsedNodeCollector $parsedNodeCollector,
        ClassManipulator $classManipulator,
        ReturnNodeVendorLockResolver $returnNodeVendorLockResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->classManipulator = $classManipulator;
        $this->returnNodeVendorLockResolver = $returnNodeVendorLockResolver;
    }

    public function isParamChangeVendorLockedIn(ClassMethod $classMethod, int $paramPosition): bool
    {
        /** @var Class_|null $classNode */
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if (! $this->hasParentClassOrImplementsInterface($classNode)) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);
        if (! is_string($methodName)) {
            throw new ShouldNotHappenException();
        }

        // @todo extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            $vendorLock = $this->isParentClassVendorLocking($paramPosition, $parentClassName, $methodName);
            if ($vendorLock !== null) {
                return $vendorLock;
            }
        }

        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_ && ! $classNode instanceof Interface_) {
            return false;
        }

        $interfaceNames = $this->classManipulator->getClassLikeNodeParentInterfaceNames($classNode);
        return $this->isInterfaceParamVendorLockin($interfaceNames, $methodName);
    }

    public function isReturnChangeVendorLockedIn(ClassMethod $classMethod): bool
    {
        return $this->returnNodeVendorLockResolver->isVendorLocked($classMethod);
    }

    public function isPropertyChangeVendorLockedIn(Property $property): bool
    {
        /** @var Class_|null $classNode */
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if (! $this->hasParentClassOrImplementsInterface($classNode)) {
            return false;
        }

        /** @var string|null $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        if (! is_string($propertyName)) {
            throw new ShouldNotHappenException();
        }

        // @todo extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            $parentClassProperty = $this->findParentProperty($parentClassName, $propertyName);

            // @todo validate type is conflicting
            // parent class property in local scope → it's ok
            if ($parentClassProperty !== null) {
                return $parentClassProperty->type !== null;
            }

            // if not, look for it's parent parent - @todo recursion

            if (property_exists($parentClassName, $propertyName)) {
                // @todo validate type is conflicting
                // parent class property in external scope → it's not ok
                return true;

                // if not, look for it's parent parent - @todo recursion
            }
        }

        return false;
    }

    /**
     * Checks for:
     * - interface required methods
     * - abstract classes reqired method
     *
     * Prevent:
     * - removing class methods, that breaks the code
     */
    public function isClassMethodRemovalVendorLocked(ClassMethod $classMethod): bool
    {
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        if (! is_string($classMethodName)) {
            throw new ShouldNotHappenException();
        }

        /** @var Class_|null $class */
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return false;
        }

        if ($this->isVendorLockedByInterface($class, $classMethodName)) {
            return true;
        }

        if ($class->extends === null) {
            return false;
        }

        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        $classParents = class_parents($className);
        foreach ($classParents as $classParent) {
            if (! class_exists($classParent)) {
                continue;
            }

            $parentClassReflection = new ReflectionClass($classParent);
            if (! $parentClassReflection->hasMethod($classMethodName)) {
                continue;
            }

            $methodReflection = $parentClassReflection->getMethod($classMethodName);
            if (! $methodReflection->isAbstract()) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function hasParentClassOrImplementsInterface(Node $classNode): bool
    {
        if (($classNode instanceof Class_ || $classNode instanceof Interface_) && $classNode->extends) {
            return true;
        }

        if ($classNode instanceof Class_) {
            return (bool) $classNode->implements;
        }

        return false;
    }

    // Until we have getProperty (https://github.com/nikic/PHP-Parser/pull/646)
    private function getProperty(ClassLike $classLike, string $name)
    {
        $lowerName = strtolower($name);

        foreach ($classLike->getProperties() as $property) {
            foreach ($property->props as $propertyProperty) {
                if ($lowerName !== $propertyProperty->name->toLowerString()) {
                    continue;
                }

                return $property;
            }
        }

        return null;
    }

    private function findParentProperty(string $parentClassName, string $propertyName): ?Property
    {
        $parentClassNode = $this->parsedNodeCollector->findClass($parentClassName);
        if ($parentClassNode === null) {
            return null;
        }

        return $this->getProperty($parentClassNode, $propertyName);
    }

    private function isVendorLockedByInterface(Class_ $class, string $classMethodName): bool
    {
        // required by interface?
        foreach ($class->implements as $implement) {
            $implementedInterfaceName = $this->nodeNameResolver->getName($implement);
            if (! is_string($implementedInterfaceName)) {
                throw new ShouldNotHappenException();
            }

            if (! interface_exists($implementedInterfaceName)) {
                continue;
            }

            $interfaceMethods = get_class_methods($implementedInterfaceName);
            if (! in_array($classMethodName, $interfaceMethods, true)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function isParentClassVendorLocking(int $paramPosition, string $parentClassName, string $methodName): ?bool
    {
        $parentClassNode = $this->parsedNodeCollector->findClass($parentClassName);
        if ($parentClassNode !== null) {
            $parentMethodNode = $parentClassNode->getMethod($methodName);
            // @todo validate type is conflicting
            // parent class method in local scope → it's ok
            if ($parentMethodNode !== null) {
                // parent method has no type → we cannot change it here
                return isset($parentMethodNode->params[$paramPosition]) && $parentMethodNode->params[$paramPosition]->type === null;
            }
        }

        // if not, look for it's parent parent - @todo recursion

        if (method_exists($parentClassName, $methodName)) {
            // @todo validate type is conflicting
            // parent class method in external scope → it's not ok
            return true;

            // if not, look for it's parent parent - @todo recursion
        }

        return null;
    }

    private function isInterfaceParamVendorLockin(array $interfaceNames, string $methodName): bool
    {
        foreach ($interfaceNames as $interfaceName) {
            $interface = $this->parsedNodeCollector->findInterface($interfaceName);
            // parent class method in local scope → it's ok
            // @todo validate type is conflicting
            if ($interface !== null && $interface->getMethod($methodName) !== null) {
                return false;
            }

            if (method_exists($interfaceName, $methodName)) {
                // parent class method in external scope → it's not ok
                // @todo validate type is conflicting
                return true;
            }
        }

        return false;
    }
}
