<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\NodeNameResolver\NodeNameResolver;

abstract class AbstractNodeVendorLockResolver
{
    /**
     * @var ParsedNodeCollector
     */
    protected $parsedNodeCollector;

    /**
     * @var ClassManipulator
     */
    protected $classManipulator;

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @required
     */
    public function autowireAbstractNodeVendorLockResolver(
        ParsedNodeCollector $parsedNodeCollector,
        ClassManipulator $classManipulator,
        NodeNameResolver $nodeNameResolver
    ): void {
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->classManipulator = $classManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    protected function hasParentClassOrImplementsInterface(ClassLike $classLike): bool
    {
        if (($classLike instanceof Class_ || $classLike instanceof Interface_) && $classLike->extends) {
            return true;
        }

        if ($classLike instanceof Class_) {
            return (bool) $classLike->implements;
        }

        return false;
    }

    /**
     * @param Class_|Interface_ $classLike
     */
    protected function isMethodVendorLockedByInterface(ClassLike $classLike, string $methodName): bool
    {
        $interfaceNames = $this->classManipulator->getClassLikeNodeParentInterfaceNames($classLike);

        foreach ($interfaceNames as $interfaceName) {
            if (! interface_exists($interfaceName)) {
                continue;
            }

            $interfaceMethods = get_class_methods($interfaceName);
            if (! in_array($methodName, $interfaceMethods, true)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
