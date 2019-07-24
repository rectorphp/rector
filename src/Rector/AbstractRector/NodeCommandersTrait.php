<?php declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Application\AppliedRectorCollector;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\PhpParser\Node\Commander\NodeAddingCommander;
use Rector\PhpParser\Node\Commander\NodeRemovingCommander;
use Rector\PhpParser\Node\Commander\PropertyAddingCommander;
use Rector\PhpParser\Node\VariableInfo;

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
    public function setRequiredCommanders(
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

    protected function addPropertyToClass(Class_ $classNode, string $propertyType, string $propertyName): void
    {
        $variableInfo = new VariableInfo($propertyName, $propertyType);
        $this->propertyAddingCommander->addPropertyToClass($variableInfo, $classNode);

        $this->notifyNodeChangeFileInfo($classNode);
    }

    protected function removeNode(Node $node): void
    {
        $this->nodeRemovingCommander->addNode($node);

        $this->notifyNodeChangeFileInfo($node);
    }

    protected function isNodeRemoved(Node $node): bool
    {
        return $this->nodeRemovingCommander->isNodeRemoved($node);
    }

    protected function addUseImport(Node $node, string $useImport): void
    {
        $this->useAddingCommander->addUseImport($node, $useImport);
    }

    protected function addFunctionUseImport(Node $node, string $functionUseImport): void
    {
        $this->useAddingCommander->addFunctionUseImport($node, $functionUseImport);
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
