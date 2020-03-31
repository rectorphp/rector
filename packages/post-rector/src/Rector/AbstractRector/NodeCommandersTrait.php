<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\PhpParser\Node\Commander\NodeAddingCommander;
use Rector\Core\PhpParser\Node\Commander\NodeReplacingCommander;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\Collector\UseNodesToAddCollector;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeCommandersTrait
{
    /**
     * @var UseNodesToAddCollector
     */
    protected $useNodesToAddCollector;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    /**
     * @var NodeAddingCommander
     */
    private $nodeAddingCommander;

    /**
     * @var PropertyToAddCollector
     */
    private $propertyToAddCollector;

    /**
     * @var NodeReplacingCommander
     */
    private $nodeReplacingCommander;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    /**
     * @required
     */
    public function autowireNodeCommandersTrait(
        NodesToRemoveCollector $nodesToRemoveCollector,
        NodeAddingCommander $nodeAddingCommander,
        PropertyToAddCollector $propertyToAddCollector,
        UseNodesToAddCollector $useNodesToAddCollector,
        NodeReplacingCommander $nodeReplacingCommander,
        RectorChangeCollector $rectorChangeCollector
    ): void {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodeAddingCommander = $nodeAddingCommander;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->nodeReplacingCommander = $nodeReplacingCommander;
        $this->rectorChangeCollector = $rectorChangeCollector;
    }

    /**
     * @param FullyQualifiedObjectType|AliasedObjectType $objectType
     */
    protected function addUseType(ObjectType $objectType, Node $positionNode): void
    {
        assert($objectType instanceof FullyQualifiedObjectType || $objectType instanceof AliasedObjectType);

        $this->useNodesToAddCollector->addUseImport($positionNode, $objectType);
    }

    protected function addNodeAfterNode(Node $newNode, Node $positionNode): void
    {
        $this->nodeAddingCommander->addNodeAfterNode($newNode, $positionNode);

        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }

    protected function addNodeBeforeNode(Node $newNode, Node $positionNode): void
    {
        $this->nodeAddingCommander->addNodeBeforeNode($newNode, $positionNode);

        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }

    protected function addPropertyToClass(Class_ $class, ?Type $propertyType, string $propertyName): void
    {
        $this->propertyToAddCollector->addPropertyToClass($propertyName, $propertyType, $class);
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }

    protected function addConstantToClass(Class_ $class, ClassConst $classConst): void
    {
        $this->propertyToAddCollector->addConstantToClass($class, $classConst);
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }

    protected function addPropertyWithoutConstructorToClass(
        Class_ $classNode,
        ?Type $propertyType,
        string $propertyName
    ): void {
        $this->propertyToAddCollector->addPropertyWithoutConstructorToClass($propertyName, $propertyType, $classNode);
        $this->rectorChangeCollector->notifyNodeFileInfo($classNode);
    }

    protected function removeNode(Node $node): void
    {
        $this->nodesToRemoveCollector->addNodeToRemove($node);
        $this->rectorChangeCollector->notifyNodeFileInfo($node);
    }

    protected function replaceNode(Node $node, Node $replaceWith): void
    {
        $this->nodeReplacingCommander->replaceNode($node, $replaceWith);

        $this->rectorChangeCollector->notifyNodeFileInfo($replaceWith);
    }

    /**
     * @param ClassLike|FunctionLike $nodeWithStatements
     */
    protected function removeNodeFromStatements(Node $nodeWithStatements, Node $nodeToRemove): void
    {
        foreach ($nodeWithStatements->stmts as $key => $stmt) {
            if ($nodeToRemove !== $stmt) {
                continue;
            }

            unset($nodeWithStatements->stmts[$key]);
            break;
        }
    }

    protected function isNodeRemoved(Node $node): bool
    {
        return $this->nodesToRemoveCollector->isNodeRemoved($node);
    }

    /**
     * @param Node[] $nodes
     */
    protected function removeNodes(array $nodes): void
    {
        foreach ($nodes as $node) {
            $this->removeNode($node);
        }
    }

    protected function notifyNodeFileInfo(Node $node): void
    {
        $this->rectorChangeCollector->notifyNodeFileInfo($node);
    }
}
