<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use Rector\NodeValueResolver\Contract\NodeValueResolverAwareInterface;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\NodeValueResolver\NodeValueResolver;

final class ConcatValueResolver implements PerNodeValueResolverInterface, NodeValueResolverAwareInterface
{
    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function getNodeClass(): string
    {
        return Concat::class;
    }

    /**
     * @param Concat $concatNode
     * @return mixed
     */
    public function resolve(Node $concatNode)
    {
        return $this->nodeValueResolver->resolve($concatNode->left) .
            $this->nodeValueResolver->resolve($concatNode->right);
    }

    public function setNodeValueResolver(NodeValueResolver $nodeValueResolver): void
    {
        $this->nodeValueResolver = $nodeValueResolver;
    }
}
