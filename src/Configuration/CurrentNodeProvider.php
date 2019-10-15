<?php

declare(strict_types=1);

namespace Rector\Configuration;

use PhpParser\Node;

final class CurrentNodeProvider
{
    /**
     * @var Node
     */
    private $node;

    public function setNode(Node $node): void
    {
        $this->node = $node;
    }

    public function getNode(): Node
    {
        return $this->node;
    }
}
