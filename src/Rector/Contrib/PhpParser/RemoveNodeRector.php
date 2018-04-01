<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\Return_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Covers: https://github.com/nikic/PHP-Parser/commit/987c61e935a7d73485b4d73aef7a17a4c1e2e325
 *
 * Before:
 *
 * public function leaveNode()
 * {
 *     return false;
 * }
 *
 * After:
 *
 * public function leaveNode()
 * {
 *     return NodeTraverser::REMOVE_NODE ;
 * }
 */
final class RemoveNodeRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Return_) {
            return false;
        }

        if (! $node->expr instanceof ConstFetch) {
            return false;
        }

        $methodName = $node->getAttribute(Attribute::METHOD_NAME);
        if ($methodName !== 'leaveNode') {
            return false;
        }

        $value = $node->expr->name->toString();

        return $value === 'false';
    }

    /**
     * @param Return_ $returnNode
     */
    public function refactor(Node $returnNode): ?Node
    {
        $returnNode->expr = $this->nodeFactory->createClassConstant('PhpParser\NodeTraverser', 'REMOVE_NODE');

        return $returnNode;
    }
}
