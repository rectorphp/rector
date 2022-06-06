<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
final class ReturnAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function hasByRefReturn(Return_ $return) : bool
    {
        $parentFunctionLike = $this->betterNodeFinder->findParentType($return, FunctionLike::class);
        if ($parentFunctionLike instanceof FunctionLike) {
            return $parentFunctionLike->returnsByRef();
        }
        return \false;
    }
}
