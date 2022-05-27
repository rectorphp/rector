<?php

declare (strict_types=1);
namespace Rector\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;
final class NodeTypeGroup
{
    /**
     * These nodes have a public $stmts property that can be iterated - based on https://github.com/rectorphp/php-parser-nodes-docs
     * @var array<class-string<Node>>
     * @api
     */
    public const STMTS_AWARE = [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\Else_::class, \PhpParser\Node\Stmt\ElseIf_::class, \PhpParser\Node\Stmt\Do_::class, \PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\Stmt\TryCatch::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\For_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\Finally_::class, \PhpParser\Node\Stmt\Case_::class, \PhpParser\Node\Stmt\Catch_::class];
}
