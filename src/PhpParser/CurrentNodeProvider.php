<?php declare(strict_types=1);

namespace Rector\PhpParser;

use PhpParser\Node;

final class CurrentNodeProvider
{
    /**
     * @var Node
     */
    private $currentNode;

    public function setCurrentNode(Node $currentNode): void
    {
        $this->currentNode = $currentNode;
    }

    public function getCurrentNode(): ?Node
    {
        return $this->currentNode;
    }
}
