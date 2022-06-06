<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Stmt\Case_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Catch_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Do_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Else_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ElseIf_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Finally_;
use RectorPrefix20220606\PhpParser\Node\Stmt\For_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Foreach_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\PhpParser\Node\Stmt\TryCatch;
use RectorPrefix20220606\PhpParser\Node\Stmt\While_;
final class NodeTypeGroup
{
    /**
     * These nodes have a public $stmts property that can be iterated - based on https://github.com/rectorphp/php-parser-nodes-docs
     * @var array<class-string<Node>>
     * @api
     */
    public const STMTS_AWARE = [ClassMethod::class, Function_::class, If_::class, Else_::class, ElseIf_::class, Do_::class, Foreach_::class, TryCatch::class, While_::class, For_::class, Closure::class, Finally_::class, Case_::class, Catch_::class];
}
