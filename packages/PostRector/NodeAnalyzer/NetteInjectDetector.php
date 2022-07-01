<?php

declare (strict_types=1);
namespace Rector\PostRector\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
final class NetteInjectDetector
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(NodeNameResolver $nodeNameResolver, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isNetteInjectPreferred(Class_ $class) : bool
    {
        if ($this->isInjectPropertyAlreadyInTheClass($class)) {
            return \true;
        }
        return $this->hasParentClassConstructor($class);
    }
    private function isInjectPropertyAlreadyInTheClass(Class_ $class) : bool
    {
        foreach ($class->getProperties() as $property) {
            if (!$property->isPublic()) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if ($phpDocInfo->hasByName('inject')) {
                return \true;
            }
        }
        return \false;
    }
    private function hasParentClassConstructor(Class_ $class) : bool
    {
        $className = (string) $this->nodeNameResolver->getName($class);
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->isSubclassOf('Nette\\Application\\IPresenter')) {
            return \false;
        }
        // has no parent class
        if ($class->extends === null) {
            return \false;
        }
        $parentClass = $this->nodeNameResolver->getName($class->extends);
        // is not the nette class - we don't care about that
        if ($parentClass === 'Nette\\Application\\UI\\Presenter') {
            return \false;
        }
        // prefer local constructor
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->hasMethod(MethodName::CONSTRUCT)) {
            $extendedMethodReflection = $classReflection->getConstructor();
            $declaringClass = $extendedMethodReflection->getDeclaringClass();
            // be sure its local constructor
            if ($declaringClass->getName() === $className) {
                return \false;
            }
        }
        $classReflection = $this->reflectionProvider->getClass($parentClass);
        return $classReflection->hasMethod(MethodName::CONSTRUCT);
    }
}
