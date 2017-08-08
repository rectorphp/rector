<?php declare(strict_types=1);

namespace Rector\NodeVisitor\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Builder\PropertyBuilder;
use Rector\NodeTraverser\TokenSwitcher;

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
     * @var ClassPropertyCollector
     */
    private $newClassPropertyCollector;

    /**
     * @var TokenSwitcher
     */
    private $tokenSwitcher;

    public function __construct(
        ConstructorMethodBuilder $constructorMethodBuilder,
        PropertyBuilder $propertyBuilder,
        ClassPropertyCollector $newClassPropertyCollector,
        TokenSwitcher $tokenSwitcher
    ) {
        $this->constructorMethodBuilder = $constructorMethodBuilder;
        $this->propertyBuilder = $propertyBuilder;
        $this->newClassPropertyCollector = $newClassPropertyCollector;
        $this->tokenSwitcher = $tokenSwitcher;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        foreach ($nodes as $key => $node) {
            if ($node instanceof Class_) {
                $nodes[$key] = $this->reconstruct($node, (string) $node->name);
                break;
            }
        }

        // this does!
        $this->tokenSwitcher->disable();

        return $nodes;
    }

    private function reconstruct(Class_ $classNode, string $className): Class_
    {
        $propertiesForClass = $this->newClassPropertyCollector->getPropertiesforClass($className);

        foreach ($propertiesForClass as $propertyType => $propertyName) {
            $this->constructorMethodBuilder->addPropertyAssignToClass($classNode, $propertyType, $propertyName);
            $this->propertyBuilder->addPropertyToClass($classNode, $propertyType, $propertyName);
        }

        return $classNode;
    }
}
