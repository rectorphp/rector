<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\Contract;

use Rector\NodeValueResolver\NodeValueResolver;

interface NodeValueResolverAwareInterface
{
    public function setNodeValueResolver(NodeValueResolver $nodeValueResolver): void;
}
