<?php declare(strict_types=1);

namespace Rector\NodeVisitor\DependencyInjection\NamedServicesToConstructor;

use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Builder\PropertyBuilder;
use Rector\Buillder\Class_\ClassPropertyCollector;

/**
 * Add new propertis to class and to contructor.
 */
final class AddPropertiesToClassNodeVisitor extends NodeVisitorAbstract
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
     * @var string
     */
    private $className;

    /**
     * @var ClassPropertyCollector
     */
    private $newClassPropertyCollector;

    public function __construct(
        ConstructorMethodBuilder $constructorMethodBuilder,
        PropertyBuilder $propertyBuilder,
        ClassPropertyCollector $newClassPropertyCollector
    ) {
        $this->constructorMethodBuilder = $constructorMethodBuilder;
        $this->propertyBuilder = $propertyBuilder;
        $this->newClassPropertyCollector = $newClassPropertyCollector;
    }

    public function afterTraverse(array $nodes): array
    {
        foreach ($nodes as $node) {
            if ($node instanceof Class_) {
                $this->className = (string) $node->name;
                $this->reconstruct($node);
            }
        }

        return $nodes;
    }

    private function reconstruct(Class_ $classNode): void
    {
        $propertiesForClass = $this->newClassPropertyCollector->getPropertiesforClass($this->className);

        foreach ($propertiesForClass as $propertyType => $propertyName) {
            $this->constructorMethodBuilder->addPropertyAssignToClass($classNode, $propertyType, $propertyName);
            $this->propertyBuilder->addPropertyToClass($classNode, $propertyType, $propertyName);
        }
    }
}
