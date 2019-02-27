<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\Node\CommanderInterface;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\VariableInfo;

/**
 * Adds new properties to class and to constructor
 */
final class PropertyAddingCommander implements CommanderInterface
{
    /**
     * @var VariableInfo[][]
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

    public function addPropertyToClass(VariableInfo $variableInfo, Class_ $classNode): void
    {
        $this->propertiesByClass[spl_object_hash($classNode)][] = $variableInfo;
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

    private function createNodeVisitor(): NodeVisitor
    {
        return new class($this->classManipulator, $this->propertiesByClass) extends NodeVisitorAbstract {
            /**
             * @var VariableInfo[][]
             */
            private $propertiesByClass = [];

            /**
             * @var ClassManipulator
             */
            private $classManipulator;

            /**
             * @param VariableInfo[][] $propertiesByClass
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
                $variableInfos = $this->propertiesByClass[spl_object_hash($classNode)] ?? [];
                foreach ($variableInfos as $propertyInfo) {
                    $this->classManipulator->addConstructorDependency($classNode, $propertyInfo);
                }

                return $classNode;
            }
        };
    }
}
