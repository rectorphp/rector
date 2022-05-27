<?php

declare (strict_types=1);
namespace Rector\Nette\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
