<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Type\Type;
use Rector\Core\Contract\PhpParser\Node\CommanderInterface;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;

/**
 * Adds new private properties to class + to constructor
 */
final class PropertyAddingCommander implements CommanderInterface
{
    /**
     * @var Type[][]|null[][]
     */
    private $propertiesByClass = [];

    /**
     * @var Type[][]|null[][]
     */
    private $propertiesWithoutConstructorByClass = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function addPropertyToClass(string $propertyName, ?Type $propertyType, Class_ $classNode): void
    {
        $this->propertiesByClass[spl_object_hash($classNode)][$propertyName] = $propertyType;
    }

    public function addPropertyWithoutConstructorToClass(
        string $propertyName,
        ?Type $propertyType,
        Class_ $classNode
    ): void {
        $this->propertiesWithoutConstructorByClass[spl_object_hash($classNode)][$propertyName] = $propertyType;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodes(array $nodes): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->createNodeVisitor());

        // new nodes to remove are always per traverse
        $this->propertiesByClass = [];

        return $nodeTraverser->traverse($nodes);
    }

    public function isActive(): bool
    {
        return count($this->propertiesByClass) > 0 || count($this->propertiesWithoutConstructorByClass) > 0;
    }

    public function getPriority(): int
    {
        return 900;
    }

    private function createNodeVisitor(): NodeVisitor
    {
        return new class($this->classManipulator, $this->propertiesByClass, $this->propertiesWithoutConstructorByClass) extends NodeVisitorAbstract {
            /**
             * @var Type[][]|null[][]
             */
            private $propertiesByClass = [];

            /**
             * @var Type[][]|null[][]
             */
            private $propertiesWithoutConstructorByClass = [];

            /**
             * @var ClassManipulator
             */
            private $classManipulator;

            /**
             * @param Type[][]|null[][] $propertiesByClass
             * @param Type[][]|null[][] $propertiesWithoutConstructorByClass
             */
            public function __construct(
                ClassManipulator $classManipulator,
                array $propertiesByClass,
                array $propertiesWithoutConstructorByClass
            ) {
                $this->classManipulator = $classManipulator;
                $this->propertiesByClass = $propertiesByClass;
                $this->propertiesWithoutConstructorByClass = $propertiesWithoutConstructorByClass;
            }

            public function enterNode(Node $node): ?Node
            {
                if (! $node instanceof Class_ || $node->isAnonymous()) {
                    return null;
                }

                return $this->processClassNode($node);
            }

            private function processClassNode(Class_ $classNode): Class_
            {
                $classProperties = $this->propertiesByClass[spl_object_hash($classNode)] ?? [];
                foreach ($classProperties as $propertyName => $propertyType) {
                    $this->classManipulator->addConstructorDependency($classNode, $propertyName, $propertyType);
                }

                $classPropertiesWithoutConstructor = $this->propertiesWithoutConstructorByClass[spl_object_hash(
                    $classNode
                )] ?? [];
                foreach ($classPropertiesWithoutConstructor as $propertyName => $propertyType) {
                    $this->classManipulator->addPropertyToClass($classNode, $propertyName, $propertyType);
                }

                return $classNode;
            }
        };
    }
}
