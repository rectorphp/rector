<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use Rector\Core\Contract\NameResolver\NodeNameResolverInterface;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;

final class ParamNameResolver implements NodeNameResolverInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @required
     */
    public function autowireClassNameResolver(NodeNameResolver $nodeNameResolver): void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function getNode(): string
    {
        return Param::class;
    }

    /**
     * @param Param $node
     */
    public function resolve(Node $node): ?string
    {
        return $this->nodeNameResolver->getName($node->var);
    }
}
