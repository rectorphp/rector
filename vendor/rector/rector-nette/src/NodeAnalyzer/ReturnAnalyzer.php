<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNestingScope\ScopeNestingComparator;
final class ReturnAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ScopeNestingComparator
     */
    private $scopeNestingComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, ScopeNestingComparator $scopeNestingComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->scopeNestingComparator = $scopeNestingComparator;
    }
    public function findLastClassMethodReturn(ClassMethod $classMethod) : ?Return_
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf($classMethod, Return_::class);
        // put the latest first
        $returns = \array_reverse($returns);
        foreach ($returns as $return) {
            if ($this->scopeNestingComparator->areReturnScopeNested($return, $classMethod)) {
                return $return;
            }
        }
        return null;
    }
    public function isBeforeLastReturn(Assign $assign, ?Return_ $lastReturn) : bool
    {
        if (!$lastReturn instanceof Return_) {
            return \true;
        }
        return $lastReturn->getStartTokenPos() < $assign->getStartTokenPos();
    }
}
