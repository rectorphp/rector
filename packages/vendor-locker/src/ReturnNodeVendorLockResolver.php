<?php

declare(strict_types=1);

namespace Rector\VendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @todo decouple to standalone package "packages/vendor-locker"
 */
final class ReturnNodeVendorLockResolver
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

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ParsedNodeCollector $parsedNodeCollector,
        ClassManipulator $classManipulator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->classManipulator = $classManipulator;
    }

    public function isVendorLocked(ClassMethod $classMethod): bool
    {
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if (! $this->hasParentClassOrImplementsInterface($classNode)) {
            return false;
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        // @todo extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            return $this->isVendorLockedByParentClass($parentClassName, $methodName);
        }

        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_ && ! $classNode instanceof Interface_) {
            return false;
        }

        $interfaceNames = $this->classManipulator->getClassLikeNodeParentInterfaceNames($classNode);

        return $this->isVendorLockedByInterface($interfaceNames, $methodName);
    }

    private function hasParentClassOrImplementsInterface(ClassLike $classLike): bool
    {
        if (($classLike instanceof Class_ || $classLike instanceof Interface_) && $classLike->extends) {
            return true;
        }

        if ($classLike instanceof Class_) {
            return (bool) $classLike->implements;
        }

        return false;
    }

    private function isVendorLockedByParentClass(string $parentClassName, string $methodName): bool
    {
        $parentClassNode = $this->parsedNodeCollector->findClass($parentClassName);
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

        return false;
    }

    /**
     * @param string[] $interfaceNames
     */
    private function isVendorLockedByInterface(array $interfaceNames, string $methodName): bool
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
