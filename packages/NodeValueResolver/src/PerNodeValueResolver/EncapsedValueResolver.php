<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use Rector\NodeValueResolver\Contract\NodeValueResolverAwareInterface;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\NodeValueResolver\NodeValueResolver;

final class EncapsedValueResolver implements PerNodeValueResolverInterface, NodeValueResolverAwareInterface
{
    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function getNodeClass(): string
    {
        return Encapsed::class;
    }

    /**
     * @param Encapsed $encapsedNode
     */
    public function resolve(Node $encapsedNode): string
    {
        $result = '';

        foreach ($encapsedNode->parts as $part) {
            if ($part instanceof EncapsedStringPart) {
                $result .= $part->value . ' ';
            } else {
                $result .= $this->nodeValueResolver->resolve($part) . ' ';
            }
        }

        return $result;
    }

    public function setNodeValueResolver(NodeValueResolver $nodeValueResolver): void
    {
        $this->nodeValueResolver = $nodeValueResolver;
    }
}
