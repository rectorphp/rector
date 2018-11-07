<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\Application\AppliedRectorCollector;
use Rector\Contract\Rector\PhpRectorInterface;

abstract class AbstractRector extends NodeVisitorAbstract implements PhpRectorInterface
{
    use TypeAnalyzerTrait;
    use NameResolverTrait;
    use ConstFetchAnalyzerTrait;
    use BetterStandardPrinterTrait;
    use NodeRemovingTrait;
    use NodeAddingTrait;
    use NodeFactoryTrait;

    /**
     * @var AppliedRectorCollector
     */
    private $appliedRectorCollector;

    /**
     * @required
     */
    public function setAbstractRectorDependencies(AppliedRectorCollector $appliedRectorCollector): void
    {
        $this->appliedRectorCollector = $appliedRectorCollector;
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

        if ($originalNode instanceof Stmt && $node instanceof Expr) {
            return new Expression($node);
        }

        return $node;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        $nodes = $this->nodeAddingCommander->traverseNodes($nodes);

        $nodes = $this->propertyAddingCommander->traverseNodes($nodes);

        return $this->nodeRemovingCommander->traverseNodes($nodes);
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
