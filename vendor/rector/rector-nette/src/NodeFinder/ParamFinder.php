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
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param Node|Node[] $nodeHaystack
     */
    public function isInAssign($nodeHaystack, \PhpParser\Node\Param $param) : bool
    {
        $variable = $param->var;
        return (bool) $this->betterNodeFinder->find($nodeHaystack, function (\PhpParser\Node $node) use($variable) : bool {
            $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parent instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node, $variable);
        });
    }
}
