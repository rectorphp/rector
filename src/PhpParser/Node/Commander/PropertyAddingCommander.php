<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Type\Type;
use Rector\Contract\PhpParser\Node\CommanderInterface;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;

/**
 * Adds new private properties to class + to constructor
 */
final class PropertyAddingCommander implements CommanderInterface
{
    /**
     * @var Type[][]
     */
    private $propertiesByClass = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function addPropertyToClass(string $propertyName, Type $propertyType, Class_ $classNode): void
    {
        $this->propertiesByClass[spl_object_hash($classNode)][$propertyName] = $propertyType;
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
        return count($this->propertiesByClass) > 0;
    }

    public function getPriority(): int
    {
        return 900;
    }

    private function createNodeVisitor(): NodeVisitor
    {
        return new class($this->classManipulator, $this->propertiesByClass) extends NodeVisitorAbstract {
            /**
             * @var Type[][]
             */
            private $propertiesByClass = [];

            /**
             * @var ClassManipulator
             */
            private $classManipulator;

            /**
             * @param Type[][] $propertiesByClass
             */
            public function __construct(ClassManipulator $classManipulator, array $propertiesByClass)
            {
                $this->classManipulator = $classManipulator;
                $this->propertiesByClass = $propertiesByClass;
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

                return $classNode;
            }
        };
    }
}
