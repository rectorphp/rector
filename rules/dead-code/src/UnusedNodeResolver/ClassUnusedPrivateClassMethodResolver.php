<?php

declare(strict_types=1);

namespace Rector\DeadCode\UnusedNodeResolver;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\NodeCollector\NodeFinder\FunctionLikeParsedNodesFinder;
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
     * @var FunctionLikeParsedNodesFinder
     */
    private $functionLikeParsedNodesFinder;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ClassManipulator $classManipulator,
        FunctionLikeParsedNodesFinder $functionLikeParsedNodesFinder
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classManipulator = $classManipulator;
        $this->functionLikeParsedNodesFinder = $functionLikeParsedNodesFinder;
    }

    /**
     * @return string[]
     */
    public function getClassUnusedMethodNames(Class_ $class): array
    {
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);

        $classMethodCalls = $this->functionLikeParsedNodesFinder->findMethodCallsOnClass($className);

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

        $interfaces = class_implements($className);

        $interfaceMethods = [];
        foreach ($interfaces as $interface) {
            $interfaceMethods = array_merge($interfaceMethods, get_class_methods($interface));
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

        $parentClasses = class_parents($class);

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
        $methodReflection = new ReflectionMethod($class, $method);

        return $methodReflection->isAbstract();
    }
}
