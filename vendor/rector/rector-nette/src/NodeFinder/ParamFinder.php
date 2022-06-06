<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\NodeFinder;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\Rector\Core\PhpParser\Comparing\NodeComparator;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class ParamFinder
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param \PhpParser\Node|mixed[] $nodeHaystack
     */
    public function isInAssign($nodeHaystack, Param $param) : bool
    {
        $variable = $param->var;
        return (bool) $this->betterNodeFinder->find($nodeHaystack, function (Node $node) use($variable) : bool {
            $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parent instanceof Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node, $variable);
        });
    }
}
