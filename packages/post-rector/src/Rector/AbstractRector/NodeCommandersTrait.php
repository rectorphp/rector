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
use Rector\CodingStyle\Application\NameImportingCommander;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\Core\PhpParser\Node\Commander\NodeAddingCommander;
use Rector\Core\PhpParser\Node\Commander\NodeReplacingCommander;
use Rector\Core\PhpParser\Node\Commander\PropertyAddingCommander;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PostRector\Collector\NodesToRemoveCollector;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeCommandersTrait
{
    /**
     * @var NameImportingCommander
     */
    protected $nameImportingCommander;

    /**
     * @var UseAddingCommander
     */
    protected $useAddingCommander;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    /**
     * @var NodeAddingCommander
     */
    private $nodeAddingCommander;

    /**
     * @var PropertyAddingCommander
     */
    private $propertyAddingCommander;

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
        PropertyAddingCommander $propertyAddingCommander,
        UseAddingCommander $useAddingCommander,
        NameImportingCommander $nameImportingCommander,
        NodeReplacingCommander $nodeReplacingCommander,
        RectorChangeCollector $rectorChangeCollector
    ): void {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodeAddingCommander = $nodeAddingCommander;
        $this->propertyAddingCommander = $propertyAddingCommander;
        $this->useAddingCommander = $useAddingCommander;
        $this->nameImportingCommander = $nameImportingCommander;
        $this->nodeReplacingCommander = $nodeReplacingCommander;
        $this->rectorChangeCollector = $rectorChangeCollector;
    }

    /**
     * @param FullyQualifiedObjectType|AliasedObjectType $objectType
     */
    protected function addUseType(ObjectType $objectType, Node $positionNode): void
    {
        assert($objectType instanceof FullyQualifiedObjectType || $objectType instanceof AliasedObjectType);

        $this->useAddingCommander->addUseImport($positionNode, $objectType);
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
        $this->propertyAddingCommander->addPropertyToClass($propertyName, $propertyType, $class);
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }

    protected function addConstantToClass(Class_ $class, ClassConst $classConst): void
    {
        $this->propertyAddingCommander->addConstantToClass($class, $classConst);
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }

    protected function addPropertyWithoutConstructorToClass(
        Class_ $classNode,
        ?Type $propertyType,
        string $propertyName
    ): void {
        $this->propertyAddingCommander->addPropertyWithoutConstructorToClass($propertyName, $propertyType, $classNode);

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
