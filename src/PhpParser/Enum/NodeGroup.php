<?php

declare (strict_types=1);
namespace Rector\PhpParser\Enum;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Block;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;
use Rector\PhpParser\Node\FileNode;
final class NodeGroup
{
    /**
     * These nodes have Stmt[] $stmts iterable public property
     *
     * If https://github.com/nikic/PHP-Parser/pull/1113 gets merged, can replace those.
     *
     * @var array<class-string<Node>>
     */
    public const STMTS_AWARE = [Block::class, Closure::class, Case_::class, Catch_::class, ClassMethod::class, Do_::class, Else_::class, ElseIf_::class, Finally_::class, For_::class, Foreach_::class, Function_::class, If_::class, Namespace_::class, TryCatch::class, While_::class, FileNode::class];
    /**
     * @var array<class-string<Node>>
     */
    public const STMTS_TO_HAVE_NEXT_NEWLINE = [ClassMethod::class, Function_::class, Property::class, If_::class, Foreach_::class, Do_::class, While_::class, For_::class, ClassConst::class, TryCatch::class, Class_::class, Trait_::class, Interface_::class, Switch_::class];
    public static function isStmtAwareNode(Node $node): bool
    {
        foreach (self::STMTS_AWARE as $stmtAwareClass) {
            if ($node instanceof $stmtAwareClass) {
                return \true;
            }
        }
        return \false;
    }
}
