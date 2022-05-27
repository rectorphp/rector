<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

use PhpParser\Node;
use Rector\Core\Contract\Rector\RectorInterface;

final class RectifiedNode
{
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function __construct(
        private readonly string $rectorClass,
        private readonly Node $node
    ) {
    }

    /**
     * @return class-string<RectorInterface>
     */
    public function getRectorClass(): string
    {
        return $this->rectorClass;
    }

    public function getNode(): Node
    {
        return $this->node;
    }
}
