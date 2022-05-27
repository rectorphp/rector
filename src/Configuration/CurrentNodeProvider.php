<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use PhpParser\Node;
final class CurrentNodeProvider
{
    /**
     * @var \PhpParser\Node|null
     */
    private $node;
    public function setNode(\PhpParser\Node $node) : void
    {
        $this->node = $node;
    }
    public function getNode() : ?\PhpParser\Node
    {
        return $this->node;
    }
}
