<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;

final class ConstFetchResolver implements PerNodeValueResolverInterface
{
    public function getNodeClass(): string
    {
        return ConstFetch::class;
    }

    /**
     * @param ConstFetch $constFetchNode
     */
    public function resolve(Node $constFetchNode): ?bool
    {
        $name = $constFetchNode->name->toString();
        if ($name === 'true') {
            return true;
        }

        if ($name === 'false') {
            return false;
        }

        if ($name === 'null') {
            return null;
        }
    }
}
