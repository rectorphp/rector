<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\ExpressionAdder;
use Rector\Builder\PropertyAdder;
use Rector\Contract\Rector\PhpRectorInterface;

abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    use TypeAnalyzerTrait;
    use NameResolverTrait;
    use ConstFetchAnalyzerTrait;
    use BetterStandardPrinterTrait;
    use ClassPropertyCollectorTrait;
    use RemovingTrait;

    /**
     * @var ExpressionAdder
     */
    private $expressionAdder;

    /**
     * @var PropertyAdder
     */
    private $propertyAdder;

    /**
     * @required
     */
    public function setAbstractRectorDependencies(PropertyAdder $propertyAdder, ExpressionAdder $expressionAdder): void
    {
        $this->propertyAdder = $propertyAdder;
        $this->expressionAdder = $expressionAdder;
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

        $newNode = $this->refactor($node);
        if ($newNode !== null) {
            return $newNode;
        }

        return null;
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
