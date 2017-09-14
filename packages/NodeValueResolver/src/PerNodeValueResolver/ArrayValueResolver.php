<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use Rector\NodeValueResolver\Contract\NodeValueResolverAwareInterface;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\NodeValueResolver\NodeValueResolver;

final class ArrayValueResolver implements PerNodeValueResolverInterface, NodeValueResolverAwareInterface
{
    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function getNodeClass(): string
    {
        return Array_::class;
    }

    /**
     * @param Array_ $arrayNode
     * @return mixed[]
     */
    public function resolve(Node $arrayNode): array
    {
        $compiledArray = [];

        foreach ($arrayNode->items as $arrayItem) {
            $compiledValue = $this->nodeValueResolver->resolve($arrayItem->value);
            if ($arrayItem->key === null) {
                $compiledArray[] = $compiledValue;

                continue;
            }

            $key = $this->nodeValueResolver->resolve($arrayItem->key);
            $compiledArray[$key] = $compiledValue;
        }

        return $compiledArray;
    }

    public function setNodeValueResolver(NodeValueResolver $nodeValueResolver): void
    {
        $this->nodeValueResolver = $nodeValueResolver;
    }
}
