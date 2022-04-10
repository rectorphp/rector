<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class ReturnAnalyzer
{
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function hasByRefReturn(Return_ $return): bool
    {
        $parentFunctionLike = $this->betterNodeFinder->findParentType($return, FunctionLike::class);
        if ($parentFunctionLike instanceof FunctionLike) {
            return $parentFunctionLike->returnsByRef();
        }

        return false;
    }
}
