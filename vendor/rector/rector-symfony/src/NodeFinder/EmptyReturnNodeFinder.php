<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFinder;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Node\BetterNodeFinder;
final class EmptyReturnNodeFinder
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function hasNoOrEmptyReturns(ClassMethod $classMethod) : bool
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        if ($returns === []) {
            return \true;
        }
        foreach ($returns as $return) {
            if ($return->expr instanceof Expr) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
