<?php declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\Type;
use Rector\Application\AppliedRectorCollector;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\PhpParser\Node\Commander\NodeAddingCommander;
use Rector\PhpParser\Node\Commander\NodeRemovingCommander;
use Rector\PhpParser\Node\Commander\PropertyAddingCommander;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 *
 * @property-read AppliedRectorCollector $appliedRectorCollector
 */
trait NodeCommandersTrait
{
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
     * @var UseAddingCommander
     */
    private $useAddingCommander;

    /**
     * @required
     */
    public function autowireNodeCommandersTrait(
        NodeRemovingCommander $nodeRemovingCommander,
        NodeAddingCommander $nodeAddingCommander,
        PropertyAddingCommander $propertyAddingCommander,
        UseAddingCommander $useAddingCommander
    ): void {
        $this->nodeRemovingCommander = $nodeRemovingCommander;
        $this->nodeAddingCommander = $nodeAddingCommander;
        $this->propertyAddingCommander = $propertyAddingCommander;
        $this->useAddingCommander = $useAddingCommander;
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

    protected function addPropertyToClass(Class_ $classNode, Type $propertyType, string $propertyName): void
    {
        $this->propertyAddingCommander->addPropertyToClass($propertyName, $propertyType, $classNode);

        $this->notifyNodeChangeFileInfo($classNode);
    }

    protected function removeNode(Node $node): void
    {
        $this->nodeRemovingCommander->addNode($node);

        $this->notifyNodeChangeFileInfo($node);
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
