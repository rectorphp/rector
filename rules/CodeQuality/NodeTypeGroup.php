<?php

declare(strict_types=1);

namespace Rector\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;

final class NodeTypeGroup
{
    /**
     * These nodes have a public $stmts property that can be iterated - based on https://github.com/rectorphp/php-parser-nodes-docs
     * @todo create a virtual node, getStmts(): array
     * @var array<class-string<Node>>
     */
    public const STMTS_AWARE = [
        ClassMethod::class, Function_::class, If_::class, Else_::class, ElseIf_::class, Do_::class, Foreach_::class, TryCatch::class, While_::class,
    ];
}
