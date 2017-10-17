<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Builder\PropertyBuilder;

/**
 * Adds new properties to class and to contructor.
 */
final class PropertyToClassAdder extends NodeVisitorAbstract
{
    /**
     * @var ConstructorMethodBuilder
     */
    private $constructorMethodBuilder;

    /**
     * @var PropertyBuilder
     */
    private $propertyBuilder;

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    public function __construct(
        ConstructorMethodBuilder $constructorMethodBuilder,
        PropertyBuilder $propertyBuilder,
        ClassPropertyCollector $classPropertyCollector
    ) {
        $this->constructorMethodBuilder = $constructorMethodBuilder;
        $this->propertyBuilder = $propertyBuilder;
        $this->classPropertyCollector = $classPropertyCollector;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        foreach ($nodes as $key => $node) {
            if ($node instanceof Class_ && ! $node->isAnonymous()) {
                $nodes[$key] = $this->processClass($node, (string) $node->name);

                break;
            }
        }

        return $nodes;
    }

    private function processClass(Class_ $classNode, string $className): Class_
    {
        $propertiesForClass = $this->classPropertyCollector->getPropertiesforClass($className);

        if (! count($propertiesForClass)) {
            return $classNode;
        }

        foreach ($propertiesForClass as $propertyType => $propertyName) {
            $this->constructorMethodBuilder->addPropertyAssignToClass($classNode, $propertyType, $propertyName);
            $this->propertyBuilder->addPropertyToClass($classNode, $propertyType, $propertyName);
        }

        return $classNode;
    }
}
