<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ArrayDimFetchTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function getNodeClass(): string
    {
        return ArrayDimFetch::class;
    }

    /**
     * @param ArrayDimFetch $arrayDimFetchNode
     * @return string[]
     */
    public function resolve(Node $arrayDimFetchNode): array
    {
        if ($arrayDimFetchNode->var instanceof MethodCall) {
            return $this->nodeTypeResolver->resolve($arrayDimFetchNode->var);
        }

        return null;
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
}
