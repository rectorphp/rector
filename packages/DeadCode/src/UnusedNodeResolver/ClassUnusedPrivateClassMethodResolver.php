<?php declare(strict_types=1);

namespace Rector\DeadCode\UnusedNodeResolver;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class ClassUnusedPrivateClassMethodResolver
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

    /**
     * @return string[]
     */
    public function getClassUnusedMethodNames(Class_ $class): array
    {
        /** @var string $className */
        $className = $this->nameResolver->getName($class);
        $classMethodCalls = $this->parsedNodesByType->findMethodCallsOnClass($className);

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

        return $this->filterOutInterfaceRequiredMethods($class, $unusedMethods);
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
        $className = $this->nameResolver->getName($class);

        $interfaces = class_implements($className);

        $interfaceMethods = [];
        foreach ($interfaces as $interface) {
            $interfaceMethods = array_merge($interfaceMethods, get_class_methods($interface));
        }

        return array_diff($unusedMethods, $interfaceMethods);
    }
}
