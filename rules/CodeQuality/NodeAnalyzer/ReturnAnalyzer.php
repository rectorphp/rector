<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class ReturnAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function hasByRefReturn(\PhpParser\Node\Stmt\Return_ $return) : bool
    {
        $parentFunctionLike = $this->betterNodeFinder->findParentType($return, \PhpParser\Node\FunctionLike::class);
        if ($parentFunctionLike instanceof \PhpParser\Node\FunctionLike) {
            return $parentFunctionLike->returnsByRef();
        }
        return \false;
    }
}
