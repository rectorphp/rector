<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\Node\CommanderInterface;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;
use Rector\PhpParser\Node\VariableInfo;

/**
 * Adds new properties to class and to constructor
 */
final class PropertyAddingCommander implements CommanderInterface
{
    /**
     * @var ClassMaintainer
     */
    private $classMaintainer;

    /**
     * @var VariableInfo[][]
     */
    private $propertiesByClass = [];

    public function __construct(ClassMaintainer $classMaintainer)
    {
        $this->classMaintainer = $classMaintainer;
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
        return new class($this->classMaintainer, $this->propertiesByClass) extends NodeVisitorAbstract {
            /**
             * @var ClassMaintainer
             */
            private $classMaintainer;

            /**
             * @var VariableInfo[][]
             */
            private $propertiesByClass = [];

            /**
             * @param VariableInfo[][] $propertiesByClass
             */
            public function __construct(ClassMaintainer $classMaintainer, array $propertiesByClass)
            {
                $this->classMaintainer = $classMaintainer;
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
                    $this->classMaintainer->addConstructorDependency($classNode, $propertyInfo);
                }

                return $classNode;
            }
        };
    }
}
