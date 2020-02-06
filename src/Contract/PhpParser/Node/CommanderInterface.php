<?php

declare(strict_types=1);

namespace Rector\Core\Contract\PhpParser\Node;

use PhpParser\Node;

interface CommanderInterface
{
    /**
     * Higher values are executed first
     */
    public function getPriority(): int;

    public function isActive(): bool;

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodes(array $nodes): array;
}
