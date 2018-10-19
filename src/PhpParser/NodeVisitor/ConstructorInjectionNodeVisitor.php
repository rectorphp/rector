<?php declare(strict_types=1);

namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Builder\PropertyBuilder;
use Rector\NodeTypeResolver\Node\Attribute;

final class ConstructorInjectionNodeVisitor extends NodeVisitorAbstract
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

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof Class_ || $node->isAnonymous()) {
            return $node;
        }

        return $this->processClassNode($node);
    }

    private function processClassNode(Class_ $classNode): Class_
    {
        $className = (string) $classNode->getAttribute(Attribute::CLASS_NAME);

        $propertiesForClass = $this->classPropertyCollector->getPropertiesForClass($className);
        if (! count($propertiesForClass)) {
            return $classNode;
        }

        foreach ($propertiesForClass as $property) {
            $this->constructorMethodBuilder->addSimplePropertyAssignToClass($classNode, $property);
            $this->propertyBuilder->addPropertyToClass($classNode, $property);
        }

        return $classNode;
    }
}
