<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract\PerNodeTypeResolver;

use PhpParser\Node;

interface PerNodeTypeResolverInterface
{
    public function getNodeClass(): string;

    /**
     * @param Node $node
     * @return string[]
     */
    public function resolve(Node $node): array;
}
