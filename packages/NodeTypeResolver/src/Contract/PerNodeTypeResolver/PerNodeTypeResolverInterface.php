<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract\PerNodeTypeResolver;

use PhpParser\Node;

interface PerNodeTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array;

    /**
     * @return string[]
     */
    public function resolve(Node $node): array;
}
