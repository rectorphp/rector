<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;

final class LNumberValueResolver implements PerNodeValueResolverInterface
{
    public function getNodeClass(): string
    {
        return LNumber::class;
    }

    /**
     * @param LNumber $lNumberNode
     * @return mixed
     */
    public function resolve(Node $lNumberNode): int
    {
        return $lNumberNode->value;
    }
}
