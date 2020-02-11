<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector\Fixture;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;

class SkipNonArrays
{
    /**
     * @var string[]
     */
    private const BREAK_NODES = [FunctionLike::class, ClassMethod::class];

    /**
     * @var string[]
     */
    private const LOOP_NODES = [For_::class, Foreach_::class, While_::class, Do_::class, Switch_::class];

    public function go()
    {
        return array_merge(self::LOOP_NODES, self::BREAK_NODES);
    }
}
