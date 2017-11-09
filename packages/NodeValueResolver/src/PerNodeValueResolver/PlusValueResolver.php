<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Plus;
use Rector\NodeValueResolver\Contract\NodeValueResolverAwareInterface;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\NodeValueResolver\NodeValueResolver;

final class PlusValueResolver implements PerNodeValueResolverInterface, NodeValueResolverAwareInterface
{
    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function getNodeClass(): string
    {
        return Plus::class;
    }

    /**
     * @param Plus $plusNode
     * @return mixed
     */
    public function resolve(Node $plusNode)
    {
        return $this->nodeValueResolver->resolve($plusNode->left) + $this->nodeValueResolver->resolve($plusNode->right);
    }

    public function setNodeValueResolver(NodeValueResolver $nodeValueResolver): void
    {
        $this->nodeValueResolver = $nodeValueResolver;
    }
}
