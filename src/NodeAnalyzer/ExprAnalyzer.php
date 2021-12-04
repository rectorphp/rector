<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class ExprAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isNonTypedFromParam(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        $functionLike = $this->betterNodeFinder->findParentType($expr, \PhpParser\Node\FunctionLike::class);
        if (!$functionLike instanceof \PhpParser\Node\FunctionLike) {
            return \false;
        }
        $params = $functionLike->getParams();
        foreach ($params as $param) {
            if (!$this->nodeComparator->areNodesEqual($param->var, $expr)) {
                continue;
            }
            return $param->type === null;
        }
        return \false;
    }
}
