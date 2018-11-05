<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;
use Rector\Application\AppliedRectorCollector;
use Rector\Contract\Rector\PhpRectorInterface;
use Rector\PhpParser\Node\Builder\ExpressionAdder;
use Rector\PhpParser\Node\Builder\PropertyAdder;

abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    use TypeAnalyzerTrait;
    use NameResolverTrait;
    use ConstFetchAnalyzerTrait;
    use BetterStandardPrinterTrait;
    use RemovingTrait;
    use NodeFactoryTrait;
    use ClassMaintainerTrait;

    /**
     * @var ExpressionAdder
     */
    private $expressionAdder;

    /**
     * @var PropertyAdder
     */
    private $propertyAdder;

    /**
     * @var AppliedRectorCollector
     */
    private $appliedRectorCollector;

    /**
     * @required
     */
    public function setAbstractRectorDependencies(
        PropertyAdder $propertyAdder,
        ExpressionAdder $expressionAdder,
        AppliedRectorCollector $appliedRectorCollector
    ): void {
        $this->propertyAdder = $propertyAdder;
        $this->expressionAdder = $expressionAdder;
        $this->appliedRectorCollector = $appliedRectorCollector;
    }

    /**
     * @param Node[] $nodes
     * @return array|Node[]|null
     */
    public function beforeTraverse(array $nodes)
    {
        $this->appliedRectorCollector->reset();
    }

    /**
     * @return int|Node|null
     */
    final public function enterNode(Node $node)
    {
        $nodeClass = get_class($node);
        if (! $this->isMatchingNodeType($nodeClass)) {
            return null;
        }

        $originalNode = $node;
        $node = $this->refactor($node);
        if ($node === null) {
            return null;
        }

        // changed!
        if ($originalNode !== $node) {
            $this->appliedRectorCollector->addRectorClass(static::class);
        }

        return $node;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        $nodes = $this->expressionAdder->addExpressionsToNodes($nodes);
        $nodes = $this->propertyAdder->addPropertiesToNodes($nodes);

        return $this->removeFromNodes($nodes);
    }

    protected function addNodeAfterNode(Expr $newNode, Node $positionNode): void
    {
        $this->expressionAdder->addNodeAfterNode($newNode, $positionNode);
    }

    private function isMatchingNodeType(string $nodeClass): bool
    {
        foreach ($this->getNodeTypes() as $nodeType) {
            if (is_a($nodeClass, $nodeType, true)) {
                return true;
            }
        }

        return false;
    }
}
