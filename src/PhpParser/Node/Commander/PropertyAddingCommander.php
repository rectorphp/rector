<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Type\Type;
use Rector\Core\Contract\PhpParser\Node\CommanderInterface;
use Rector\Core\PhpParser\Node\Manipulator\ClassDependencyManipulator;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\NodeNameResolver\NodeNameResolver;

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
     * @var ClassConst[][]
     */
    private $constantsByClass = [];

    /**
     * @var Type[][]|null[][]
     */
    private $propertiesWithoutConstructorByClass = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ClassDependencyManipulator
     */
    private $classDependencyManipulator;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ClassDependencyManipulator $classDependencyManipulator,
        ClassInsertManipulator $classInsertManipulator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
    }

    public function addPropertyToClass(string $propertyName, ?Type $propertyType, Class_ $classNode): void
    {
        $this->propertiesByClass[spl_object_hash($classNode)][$propertyName] = $propertyType;
    }

    public function addConstantToClass(Class_ $class, ClassConst $classConst): void
    {
        $constantName = $this->nodeNameResolver->getName($classConst);
        $this->constantsByClass[spl_object_hash($class)][$constantName] = $classConst;
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
        $this->constantsByClass = [];

        return $nodeTraverser->traverse($nodes);
    }

    public function isActive(): bool
    {
        return count($this->propertiesByClass) > 0 || count($this->propertiesWithoutConstructorByClass) > 0 || count(
            $this->constantsByClass
        ) > 0;
    }

    public function getPriority(): int
    {
        return 900;
    }

    private function createNodeVisitor(): NodeVisitor
    {
        return new class($this->classInsertManipulator, $this->classDependencyManipulator, $this->propertiesByClass, $this->propertiesWithoutConstructorByClass, $this->constantsByClass) extends NodeVisitorAbstract {
            /**
             * @var Type[][]|null[][]
             */
            private $propertiesByClass = [];

            /**
             * @var Type[][]|null[][]
             */
            private $propertiesWithoutConstructorByClass = [];

            /**
             * @var ClassInsertManipulator
             */
            private $classInsertManipulator;

            /**
             * @var ClassConst[][]
             */
            private $constantsByClass = [];

            /**
             * @var ClassDependencyManipulator
             */
            private $classDependencyManipulator;

            /**
             * @param Type[][]|null[][] $propertiesByClass
             * @param Type[][]|null[][] $propertiesWithoutConstructorByClass
             * @param ClassConst[][] $constantsByClass
             */
            public function __construct(
                ClassInsertManipulator $classInsertManipulator,
                ClassDependencyManipulator $classDependencyManipulator,
                array $propertiesByClass,
                array $propertiesWithoutConstructorByClass,
                array $constantsByClass
            ) {
                $this->classInsertManipulator = $classInsertManipulator;
                $this->classDependencyManipulator = $classDependencyManipulator;
                $this->propertiesByClass = $propertiesByClass;
                $this->propertiesWithoutConstructorByClass = $propertiesWithoutConstructorByClass;
                $this->constantsByClass = $constantsByClass;
            }

            public function enterNode(Node $node): ?Node
            {
                if (! $node instanceof Class_ || $node->isAnonymous()) {
                    return null;
                }

                return $this->processClassNode($node);
            }

            private function processClassNode(Class_ $class): Class_
            {
                $classHash = spl_object_hash($class);

                $classConstants = $this->constantsByClass[$classHash] ?? [];
                foreach ($classConstants as $constantName => $classConst) {
                    $this->classInsertManipulator->addConstantToClass($class, $constantName, $classConst);
                }

                $classProperties = $this->propertiesByClass[$classHash] ?? [];
                foreach ($classProperties as $propertyName => $propertyType) {
                    $this->classDependencyManipulator->addConstructorDependency($class, $propertyName, $propertyType);
                }

                $classPropertiesWithoutConstructor = $this->propertiesWithoutConstructorByClass[$classHash] ?? [];
                foreach ($classPropertiesWithoutConstructor as $propertyName => $propertyType) {
                    $this->classInsertManipulator->addPropertyToClass($class, $propertyName, $propertyType);
                }

                return $class;
            }
        };
    }
}
