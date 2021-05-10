<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Rector;

use PhpParser\Node;
use PhpParser\NodeVisitor;
interface PhpRectorInterface extends \PhpParser\NodeVisitor, \Rector\Core\Contract\Rector\RectorInterface
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
    public function refactor(\PhpParser\Node $node);
}
