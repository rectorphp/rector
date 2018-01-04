<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\NodeTraverser;
use Rector\NodeVisitor\ExpressionPrependingNodeVisitor;

/**
 * This class collects all to-be-added expresssions (expression ~= 1 line in code)
 * and then prepends new expressions to list of $nodes
 *
 * From:
 * - $this->someCall();
 *
 * To:
 *
 * - $this->someCall();
 * - $value = this->someNewCall(); // added expression
 */
final class ExpressionPrepender
{
    /**
     * @var ExpressionPrependingNodeVisitor
     */
    private $expressionPrependingNodeVisitor;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(ExpressionPrependingNodeVisitor $expressionPrependingNodeVisitor)
    {
        $this->expressionPrependingNodeVisitor = $expressionPrependingNodeVisitor;

        $this->nodeTraverser = new NodeTraverser();
        $this->nodeTraverser->addVisitor($expressionPrependingNodeVisitor);
    }

    public function prependNodeAfterNode($nodeToPrepend, $positionNode): void
    {
        $this->expressionPrependingNodeVisitor->prependNodeAfterNode($nodeToPrepend, $positionNode);
    }

    /**
     * @param array $nodes
     * @return mixed
     */
    public function prependExpressionsToNodes(array $nodes)
    {
        return $this->nodeTraverser->traverse($nodes);
    }

    public function clear(): void
    {
        $this->expressionPrependingNodeVisitor->clear();
    }
}
