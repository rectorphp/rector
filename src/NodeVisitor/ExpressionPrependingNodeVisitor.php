<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use SplObjectStorage;

final class ExpressionPrependingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var SplObjectStorage
     */
    private $expressionsToPrepend;

    public function setExpressionsToPrepend(SplObjectStorage $expressionsToPrepend)
    {
        $this->expressionsToPrepend = $expressionsToPrepend;
    }

    /**
     * @return Node[]|Node
     */
    public function leaveNode(Node $node)
    {
        if (! isset($this->expressionsToPrepend[$node])) {
            return $node;
        }

        return array_merge([$node], $this->expressionsToPrepend[$node]);
    }
}
