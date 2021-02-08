<?php

declare(strict_types=1);

namespace Rector\DeadCode\UnusedNodeResolver;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use ReflectionMethod;

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

    public function __construct(
        ClassManipulator $classManipulator,
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classManipulator = $classManipulator;
        $this->nodeRepository = $nodeRepository;
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

        /** @var string[] $interfaces */
        $interfaces = (array) class_implements($className);

        $interfaceMethods = [];
        foreach ($interfaces as $interface) {
            $currentInterfaceMethods = get_class_methods($interface);
            $interfaceMethods = array_merge($interfaceMethods, $currentInterfaceMethods);
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

        /** @var string[] $parentClasses */
        $parentClasses = (array) class_parents($class);

        $parentAbstractMethods = [];

        foreach ($parentClasses as $parentClass) {
            foreach ($unusedMethods as $unusedMethod) {
                if (in_array($unusedMethod, $parentAbstractMethods, true)) {
                    continue;
                }

                if ($this->isMethodAbstract($parentClass, $unusedMethod)) {
                    $parentAbstractMethods[] = $unusedMethod;
                }
            }
        }

        return array_diff($unusedMethods, $parentAbstractMethods);
    }

    private function isMethodAbstract(string $class, string $method): bool
    {
        if (! method_exists($class, $method)) {
            return false;
        }
        $reflectionMethod = new ReflectionMethod($class, $method);

        return $reflectionMethod->isAbstract();
    }
}
