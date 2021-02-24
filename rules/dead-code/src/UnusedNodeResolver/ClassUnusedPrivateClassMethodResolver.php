<?php

declare(strict_types=1);

namespace Rector\DeadCode\UnusedNodeResolver;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassUnusedPrivateClassMethodResolver
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
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(
        ClassManipulator $classManipulator,
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository,
        ReflectionProvider $reflectionProvider
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classManipulator = $classManipulator;
        $this->nodeRepository = $nodeRepository;
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @return string[]
     */
    public function getClassUnusedMethodNames(Class_ $class): array
    {
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);

        $classMethodCalls = $this->nodeRepository->findMethodCallsOnClass($className);

        $usedMethodNames = array_keys($classMethodCalls);
        $classPublicMethodNames = $this->classManipulator->getPublicMethodNames($class);

        return $this->getUnusedMethodNames($class, $classPublicMethodNames, $usedMethodNames);
    }

    /**
     * @param string[] $classPublicMethodNames
     * @param string[] $usedMethodNames
     * @return string[]
     */
    private function getUnusedMethodNames(Class_ $class, array $classPublicMethodNames, array $usedMethodNames): array
    {
        $unusedMethods = array_diff($classPublicMethodNames, $usedMethodNames);

        $unusedMethods = $this->filterOutSystemMethods($unusedMethods);
        $unusedMethods = $this->filterOutInterfaceRequiredMethods($class, $unusedMethods);

        return $this->filterOutParentAbstractMethods($class, $unusedMethods);
    }

    /**
     * @param string[] $unusedMethods
     * @return string[]
     */
    private function filterOutSystemMethods(array $unusedMethods): array
    {
        foreach ($unusedMethods as $key => $unusedMethod) {
            // skip Doctrine-needed methods
            if (in_array($unusedMethod, ['getId', 'setId'], true)) {
                unset($unusedMethods[$key]);
            }

            // skip magic methods
            if (Strings::startsWith($unusedMethod, '__')) {
                unset($unusedMethods[$key]);
            }
        }

        return $unusedMethods;
    }

    /**
     * @param string[] $unusedMethods
     * @return string[]
     */
    private function filterOutInterfaceRequiredMethods(Class_ $class, array $unusedMethods): array
    {
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);
        $classReflection = $this->reflectionProvider->getClass($className);

        $interfaceMethods = [];
        foreach ($classReflection->getInterfaces() as $interfaceClassReflection) {
            $nativeInterfaceClassReflection = $interfaceClassReflection->getNativeReflection();
            foreach ($nativeInterfaceClassReflection->getMethods() as $reflectionMethod) {
                $interfaceMethods[] = $reflectionMethod->getName();
            }
        }

        return array_diff($unusedMethods, $interfaceMethods);
    }

    /**
     * @param string[] $unusedMethods
     * @return string[]
     */
    private function filterOutParentAbstractMethods(Class_ $class, array $unusedMethods): array
    {
        if ($class->extends === null) {
            return $unusedMethods;
        }

        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return [];
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        $parentAbstractMethods = [];

        foreach ($classReflection->getParents() as $parentClassReflection) {
            foreach ($unusedMethods as $unusedMethod) {
                if (in_array($unusedMethod, $parentAbstractMethods, true)) {
                    continue;
                }

                if ($this->isMethodAbstract($parentClassReflection, $unusedMethod)) {
                    $parentAbstractMethods[] = $unusedMethod;
                }
            }
        }

        return array_diff($unusedMethods, $parentAbstractMethods);
    }

    private function isMethodAbstract(ClassReflection $classReflection, string $method): bool
    {
        if (! $classReflection->hasMethod($method)) {
            return false;
        }

        $nativeClassReflection = $classReflection->getNativeReflection();
        $reflectionMethod = $nativeClassReflection->getMethod($method);

        return $reflectionMethod->isAbstract();
    }
}
