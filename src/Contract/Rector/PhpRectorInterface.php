<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Contract\Rector;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\NodeVisitor;
interface PhpRectorInterface extends NodeVisitor, RectorInterface
{
    /**
     * List of nodes this class checks, classes that implements \PhpParser\Node
     * See beautiful map of all nodes https://github.com/rectorphp/php-parser-nodes-docs#node-overview
     *
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array;
    /**
     * Process Node of matched type
     * @return Node|Node[]|null
     */
    public function refactor(Node $node);
}
