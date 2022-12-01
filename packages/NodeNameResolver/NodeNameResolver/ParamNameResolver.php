<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix202212\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements NodeNameResolverInterface<Param>
 */
final class ParamNameResolver implements NodeNameResolverInterface
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @required
     */
    public function autowire(NodeNameResolver $nodeNameResolver) : void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function getNode() : string
    {
        return Param::class;
    }
    /**
     * @param Param $node
     */
    public function resolve(Node $node) : ?string
    {
        return $this->nodeNameResolver->getName($node->var);
    }
}
