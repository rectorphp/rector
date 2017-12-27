<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract\PerNodeCallerTypeResolver;

use PhpParser\Node;

interface PerNodeCallerTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array;

    /**
     * @return string[]
     */
    public function resolve(Node $node): array;
}
