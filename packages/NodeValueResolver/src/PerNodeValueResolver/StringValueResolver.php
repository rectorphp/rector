<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;

final class StringValueResolver implements PerNodeValueResolverInterface
{
    public function getNodeClass(): string
    {
        return String_::class;
    }

    /**
     * @param String_ $arrayNode
     */
    public function resolve(Node $arrayNode): string
    {
        return (string) $arrayNode->value;
    }
}
