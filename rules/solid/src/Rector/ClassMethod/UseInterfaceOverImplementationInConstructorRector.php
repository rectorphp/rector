<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\UseInterfaceOverImplementationInConstructorRectorTest
 */
final class UseInterfaceOverImplementationInConstructorRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use interface instead of specific class', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function __construct(SomeImplementation $someImplementation)
    {
    }
}

class SomeImplementation implements SomeInterface
{
}

interface SomeInterface
{
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function __construct(SomeInterface $someImplementation)
    {
    }
}

class SomeImplementation implements SomeInterface
{
}

interface SomeInterface
{
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, '__construct')) {
            return null;
        }

        foreach ($node->params as $param) {
            if ($param->type === null) {
                continue;
            }

            $typeName = $this->getName($param->type);
            if ($typeName === null) {
                continue;
            }

            if (! class_exists($typeName)) {
                continue;
            }

            $implementedInterfaces = class_implements($typeName);
            if ($implementedInterfaces === []) {
                continue;
            }

            $interfaceNames = $this->getClassDirectInterfaces($typeName);
            $interfaceNames = $this->filterOutInterfaceThatHaveTwoAndMoreImplementers($interfaceNames);

            if (count($interfaceNames) !== 1) {
                continue;
            }

            $param->type = new FullyQualified($interfaceNames[0]);
        }

        return $node;
    }

    /**
     * @return string[]
     */
    private function getClassDirectInterfaces(string $typeName): array
    {
        $interfaceNames = class_implements($typeName);

        foreach ($interfaceNames as $possibleDirectInterfaceName) {
            foreach ($interfaceNames as $key => $interfaceName) {
                if ($possibleDirectInterfaceName === $interfaceName) {
                    continue;
                }

                if (! is_a($possibleDirectInterfaceName, $interfaceName, true)) {
                    continue;
                }

                unset($interfaceNames[$key]);
            }
        }

        return array_values($interfaceNames);
    }

    /**
     * 2 and more classes that implement the same interface → better skip it, the particular implementation is used on purpose probably
     *
     * @param string[] $interfaceNames
     * @return string[]
     */
    private function filterOutInterfaceThatHaveTwoAndMoreImplementers(array $interfaceNames): array
    {
        $classes = get_declared_classes();
        foreach ($interfaceNames as $key => $interfaceName) {
            $implementations = [];
            foreach ($classes as $class) {
                $interfacesImplementedByClass = class_implements($class);
                if (! in_array($interfaceName, $interfacesImplementedByClass, true)) {
                    continue;
                }

                $implementations[] = $class;
            }

            if (count($implementations) > 1) {
                unset($interfaceNames[$key]);
            }
        }

        return array_values($interfaceNames);
    }
}
