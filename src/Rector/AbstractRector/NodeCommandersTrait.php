<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\CodingStyle\Application\NameImportingCommander;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\Core\Application\AppliedRectorCollector;
use Rector\Core\PhpParser\Node\Commander\NodeAddingCommander;
use Rector\Core\PhpParser\Node\Commander\NodeRemovingCommander;
use Rector\Core\PhpParser\Node\Commander\NodeReplacingCommander;
use Rector\Core\PhpParser\Node\Commander\PropertyAddingCommander;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 *
 * @property-read AppliedRectorCollector $appliedRectorCollector
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
     * @var NodeRemovingCommander
     */
    private $nodeRemovingCommander;

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
     * @required
     */
    public function autowireNodeCommandersTrait(
        NodeRemovingCommander $nodeRemovingCommander,
        NodeAddingCommander $nodeAddingCommander,
        PropertyAddingCommander $propertyAddingCommander,
        UseAddingCommander $useAddingCommander,
        NameImportingCommander $nameImportingCommander,
        NodeReplacingCommander $nodeReplacingCommander
    ): void {
        $this->nodeRemovingCommander = $nodeRemovingCommander;
        $this->nodeAddingCommander = $nodeAddingCommander;
        $this->propertyAddingCommander = $propertyAddingCommander;
        $this->useAddingCommander = $useAddingCommander;
        $this->nameImportingCommander = $nameImportingCommander;
        $this->nodeReplacingCommander = $nodeReplacingCommander;
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

        $this->notifyNodeChangeFileInfo($positionNode);
    }

    protected function addNodeBeforeNode(Node $newNode, Node $positionNode): void
    {
        $this->nodeAddingCommander->addNodeBeforeNode($newNode, $positionNode);

        $this->notifyNodeChangeFileInfo($positionNode);
    }

    protected function addPropertyToClass(Class_ $classNode, ?Type $propertyType, string $propertyName): void
    {
        $this->propertyAddingCommander->addPropertyToClass($propertyName, $propertyType, $classNode);

        $this->notifyNodeChangeFileInfo($classNode);
    }

    protected function addPropertyWithoutConstructorToClass(
        Class_ $classNode,
        ?Type $propertyType,
        string $propertyName
    ): void {
        $this->propertyAddingCommander->addPropertyWithoutConstructorToClass($propertyName, $propertyType, $classNode);

        $this->notifyNodeChangeFileInfo($classNode);
    }

    protected function removeNode(Node $node): void
    {
        $this->nodeRemovingCommander->addNode($node);

        $this->notifyNodeChangeFileInfo($node);
    }

    protected function replaceNode(Node $node, Node $replaceWith): void
    {
        $this->nodeReplacingCommander->replaceNode($node, $replaceWith);

        $this->notifyNodeChangeFileInfo($replaceWith);
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
        return $this->nodeRemovingCommander->isNodeRemoved($node);
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
}
