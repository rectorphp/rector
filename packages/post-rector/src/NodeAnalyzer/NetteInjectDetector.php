<?php

declare(strict_types=1);

namespace Rector\PostRector\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette\NetteInjectTagNode;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use ReflectionClass;
use ReflectionMethod;

final class NetteInjectDetector
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(NodeNameResolver $nodeNameResolver, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function isNetteInjectPreferred(Class_ $class): bool
    {
        if ($this->isInjectPropertyAlreadyInTheClass($class)) {
            return true;
        }

        return $this->hasParentClassConstructor($class);
    }

    private function isInjectPropertyAlreadyInTheClass(Class_ $class): bool
    {
        foreach ($class->getProperties() as $property) {
            if (! $property->isPublic()) {
                continue;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if ($phpDocInfo->hasByType(NetteInjectTagNode::class)) {
                return true;
            }
        }

        return false;
    }

    private function hasParentClassConstructor(Class_ $class): bool
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return false;
        }

        if (! is_a($className, 'Nette\Application\IPresenter', true)) {
            return false;
        }

        // has parent class
        if ($class->extends === null) {
            return false;
        }

        $parentClass = $this->nodeNameResolver->getName($class->extends);
        // is not the nette class - we don't care about that
        if ($parentClass === 'Nette\Application\UI\Presenter') {
            return false;
        }

        // prefer local constructor
        $classReflection = new ReflectionClass($className);
        if ($classReflection->hasMethod(MethodName::CONSTRUCT)) {
            /** @var ReflectionMethod $constructorReflectionMethod */
            $constructorReflectionMethod = $classReflection->getConstructor();

            // be sure its local constructor
            if ($constructorReflectionMethod->class === $className) {
                return false;
            }
        }

        $classReflection = new ReflectionClass($parentClass);
        return $classReflection->hasMethod(MethodName::CONSTRUCT);
    }
}
