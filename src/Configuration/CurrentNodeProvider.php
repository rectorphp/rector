<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Configuration;

use RectorPrefix20220606\PhpParser\Node;
final class CurrentNodeProvider
{
    /**
     * @var \PhpParser\Node|null
     */
    private $node;
    public function setNode(Node $node) : void
    {
        $this->node = $node;
    }
    public function getNode() : ?Node
    {
        return $this->node;
    }
}
