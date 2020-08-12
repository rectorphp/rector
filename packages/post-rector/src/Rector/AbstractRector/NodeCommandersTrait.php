<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\PostRector\Collector\NodesToReplaceCollector;
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
     * @var NodesToAddCollector
     */
    private $nodesToAddCollector;

    /**
     * @var PropertyToAddCollector
     */
    private $propertyToAddCollector;

    /**
     * @var NodesToReplaceCollector
     */
    private $nodesToReplaceCollector;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @required
     */
    public function autowireNodeCommandersTrait(
        NodesToRemoveCollector $nodesToRemoveCollector,
        PropertyToAddCollector $propertyToAddCollector,
        UseNodesToAddCollector $useNodesToAddCollector,
        NodesToAddCollector $nodesToAddCollector,
        NodesToReplaceCollector $nodesToReplaceCollector,
        RectorChangeCollector $rectorChangeCollector,
        PropertyNaming $propertyNaming
    ): void {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->nodesToReplaceCollector = $nodesToReplaceCollector;
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->propertyNaming = $propertyNaming;
    }

    /**
     * @param FullyQualifiedObjectType|AliasedObjectType $objectType
     */
    protected function addUseType(ObjectType $objectType, Node $positionNode): void
    {
        assert($objectType instanceof FullyQualifiedObjectType || $objectType instanceof AliasedObjectType);

        $this->useNodesToAddCollector->addUseImport($positionNode, $objectType);
    }

    /**
     * @param Node[] $newNodes
     */
    protected function addNodesAfterNode(array $newNodes, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodesAfterNode($newNodes, $positionNode);
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }

    /**
     * @param Node[] $newNodes
     */
    protected function addNodesBeforeNode(array $newNodes, Node $positionNode): void
    {
        foreach ($newNodes as $newNode) {
            $this->addNodeBeforeNode($newNode, $positionNode);
        }
    }

    protected function addNodeAfterNode(Node $newNode, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodeAfterNode($newNode, $positionNode);
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }

    protected function addNodeBeforeNode(Node $newNode, Node $positionNode): void
    {
        $this->nodesToAddCollector->addNodeBeforeNode($newNode, $positionNode);
        $this->rectorChangeCollector->notifyNodeFileInfo($positionNode);
    }

    protected function addPropertyToCollector(Property $property): void
    {
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return;
        }

        $propertyType = $this->getObjectType($property);

        // use first type - hard assumption @todo improve
        if ($propertyType instanceof UnionType) {
            $propertyType = $propertyType->getTypes()[0];
        }

        /** @var string $propertyName */
        $propertyName = $this->getName($property);

        $this->addConstructorDependencyToClass($classNode, $propertyType, $propertyName);
    }

    protected function addServiceConstructorDependencyToClass(Class_ $class, string $className): void
    {
        $serviceObjectType = new ObjectType($className);

        $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);
        $this->addConstructorDependencyToClass($class, $serviceObjectType, $propertyName);
    }

    protected function addConstructorDependencyToClass(Class_ $class, ?Type $propertyType, string $propertyName): void
    {
        $this->propertyToAddCollector->addPropertyToClass($propertyName, $propertyType, $class);
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }

    protected function addConstantToClass(Class_ $class, ClassConst $classConst): void
    {
        $this->propertyToAddCollector->addConstantToClass($class, $classConst);
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }

    protected function addPropertyToClass(Class_ $class, ?Type $propertyType, string $propertyName): void
    {
        $this->propertyToAddCollector->addPropertyWithoutConstructorToClass($propertyName, $propertyType, $class);
        $this->rectorChangeCollector->notifyNodeFileInfo($class);
    }

    protected function removeNode(Node $node): void
    {
        // this make sure to keep just added nodes, e.g. added class constant, that doesn't have analysis of full code in this run
        // if this is missing, there are false positive e.g. for unused private constant
        $isJustAddedNode = ! (bool) $node->getAttribute(AttributeKey::ORIGINAL_NODE);
        if ($isJustAddedNode) {
            return;
        }

        $this->nodesToRemoveCollector->addNodeToRemove($node);
        $this->rectorChangeCollector->notifyNodeFileInfo($node);
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
