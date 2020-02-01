<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\VendorLock;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;
use ReflectionClass;

final class VendorLockResolver
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(
        NameResolver $nameResolver,
        ParsedNodesByType $parsedNodesByType,
        ClassManipulator $classManipulator
    ) {
        $this->nameResolver = $nameResolver;
        $this->parsedNodesByType = $parsedNodesByType;
        $this->classManipulator = $classManipulator;
    }

    public function isParameterChangeVendorLockedIn(ClassMethod $classMethod, int $paramPosition): bool
    {
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if (! $this->hasParentClassOrImplementsInterface($classNode)) {
            return false;
        }

        $methodName = $this->nameResolver->getName($classMethod);

        // @todo extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            $parentClassNode = $this->parsedNodesByType->findClass($parentClassName);
            if ($parentClassNode !== null) {
                $parentMethodNode = $parentClassNode->getMethod($methodName);
                // @todo validate type is conflicting
                // parent class method in local scope → it's ok
                if ($parentMethodNode !== null) {
                    // parent method has no type → we cannot change it here
                    return isset($parentMethodNode->params[$paramPosition]) && $parentMethodNode->params[$paramPosition]->type === null;
                }

                // if not, look for it's parent parent - @todo recursion
            }

            if (method_exists($parentClassName, $methodName)) {
                // @todo validate type is conflicting
                // parent class method in external scope → it's not ok
                return true;

                // if not, look for it's parent parent - @todo recursion
            }
        }

        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_ && ! $classNode instanceof Interface_) {
            return false;
        }

        $interfaceNames = $this->classManipulator->getClassLikeNodeParentInterfaceNames($classNode);
        foreach ($interfaceNames as $interfaceName) {
            $interface = $this->parsedNodesByType->findInterface($interfaceName);
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

    public function isReturnChangeVendorLockedIn(ClassMethod $classMethod): bool
    {
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if (! $this->hasParentClassOrImplementsInterface($classNode)) {
            return false;
        }

        $methodName = $this->nameResolver->getName($classMethod);

        // @todo extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            $parentClassNode = $this->parsedNodesByType->findClass($parentClassName);
            if ($parentClassNode !== null) {
                $parentMethodNode = $parentClassNode->getMethod($methodName);
                // @todo validate type is conflicting
                // parent class method in local scope → it's ok
                if ($parentMethodNode !== null) {
                    return $parentMethodNode->returnType !== null;
                }

                // if not, look for it's parent parent - @todo recursion
            }

            if (method_exists($parentClassName, $methodName)) {
                // @todo validate type is conflicting
                // parent class method in external scope → it's not ok
                return true;

                // if not, look for it's parent parent - @todo recursion
            }
        }

        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_ && ! $classNode instanceof Interface_) {
            return false;
        }

        $interfaceNames = $this->classManipulator->getClassLikeNodeParentInterfaceNames($classNode);
        foreach ($interfaceNames as $interfaceName) {
            $interface = $this->parsedNodesByType->findInterface($interfaceName);
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

    public function isPropertyChangeVendorLockedIn(Property $property): bool
    {
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if (! $this->hasParentClassOrImplementsInterface($classNode)) {
            return false;
        }

        $propertyName = $this->nameResolver->getName($property);

        // @todo extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            $parentClassNode = $this->parsedNodesByType->findClass($parentClassName);
            if ($parentClassNode !== null) {
                $parentPropertyNode = $this->getProperty($parentClassNode, $propertyName);
                // @todo validate type is conflicting
                // parent class property in local scope → it's ok
                if ($parentPropertyNode !== null) {
                    return $parentPropertyNode->type !== null;
                }

                // if not, look for it's parent parent - @todo recursion
            }

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
        $classMethodName = $this->nameResolver->getName($classMethod);

        /** @var Class_|null $class */
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return false;
        }

        // required by interface?
        foreach ($class->implements as $implement) {
            $implementedInterfaceName = $this->nameResolver->getName($implement);

            if (interface_exists($implementedInterfaceName)) {
                $interfaceMethods = get_class_methods($implementedInterfaceName);
                if (in_array($classMethodName, $interfaceMethods, true)) {
                    return true;
                }
            }
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

    // Until we have getProperty (https://github.com/nikic/PHP-Parser/pull/646)
    private function getProperty(ClassLike $classLike, string $name)
    {
        $lowerName = strtolower($name);
        foreach ($classLike->stmts as $stmt) {
            if ($stmt instanceof Property) {
                foreach ($stmt->props as $prop) {
                    if ($prop instanceof PropertyProperty && $lowerName === $prop->name->toLowerString()) {
                        return $stmt;
                    }
                }
            }
        }
        return null;
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
}
