<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract;

use Rector\NodeTypeResolver\NodeTypeResolver;

interface NodeTypeResolverAwareInterface
{
    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void;
}
