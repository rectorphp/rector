<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeFinder;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
final class EmptyReturnNodeFinder
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
