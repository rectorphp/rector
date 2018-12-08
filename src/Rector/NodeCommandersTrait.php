<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use Rector\Application\AppliedRectorCollector;
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
     * @required
     */
    public function setRequiredCommanders(
        NodeRemovingCommander $nodeRemovingCommander,
        NodeAddingCommander $nodeAddingCommander,
        PropertyAddingCommander $propertyAddingCommander
    ): void {
        $this->nodeRemovingCommander = $nodeRemovingCommander;
        $this->nodeAddingCommander = $nodeAddingCommander;
        $this->propertyAddingCommander = $propertyAddingCommander;
    }

    protected function addNodeAfterNode(Expr $node, Node $positionNode): void
    {
        $this->nodeAddingCommander->addNodeAfterNode($node, $positionNode);

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
}
