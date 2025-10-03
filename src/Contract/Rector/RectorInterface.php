<?php

declare (strict_types=1);
namespace Rector\Contract\Rector;

use PhpParser\Node;
use PhpParser\NodeVisitor;

interface RectorInterface extends NodeVisitor
{
    /**
     * List of nodes this class checks, classes that implements \PhpParser\Node
     * See beautiful map of all nodes https://github.com/rectorphp/php-parser-nodes-docs#node-overview
     *
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array;
    /**
     * Process Node of matched type
     * @return Node|Node[]|null|int
     *
     * For int return, choose:
     *
     *   ✔️ To decorate current node and its children to not be traversed on current rule, return one of:
     *          - NodeVisitor::DONT_TRAVERSE_CHILDREN
     *          - NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN
     *
     *   ✔️ To remove node of Node\Stmt or Node\Param, return:
     *          - NodeVisitor::REMOVE_NODE
     */
    public function refactor(Node $node);
}
