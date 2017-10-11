<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Arg;
use Rector\NodeValueResolver\Contract\NodeValueResolverAwareInterface;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\NodeValueResolver\NodeValueResolver;

final class ArgValueResolver implements PerNodeValueResolverInterface, NodeValueResolverAwareInterface
{
    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function getNodeClass(): string
    {
        return Arg::class;
    }

    /**
     * @param Arg $argNode
     */
    public function resolve(Node $argNode): string
    {
        return $this->nodeValueResolver->resolve($argNode->value);
    }

    public function setNodeValueResolver(NodeValueResolver $nodeValueResolver): void
    {
        $this->nodeValueResolver = $nodeValueResolver;
    }
}
