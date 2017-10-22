<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract\PerNodeTypeResolver;

use PhpParser\Node;

interface PerNodeTypeResolverInterface
{
    public function getNodeClass(): string;

    /**
     * @return mixed
     */
    public function resolve(Node $arrayNode);
}
