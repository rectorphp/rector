<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ContextAnalyzer
{
    /**
     * Nodes that break the scope they way up, e.g. class method
     * @var array<class-string<FunctionLike>>
     */
    private const BREAK_NODES = [\PhpParser\Node\FunctionLike::class, \PhpParser\Node\Stmt\ClassMethod::class];
    /**
     * @var array<class-string<Stmt>>
     */
    private const LOOP_NODES = [\PhpParser\Node\Stmt\For_::class, \PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\Do_::class];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isInLoop(\PhpParser\Node $node) : bool
    {
        $stopNodes = \array_merge(self::LOOP_NODES, self::BREAK_NODES);
        $firstParent = $this->betterNodeFinder->findParentByTypes($node, $stopNodes);
        if (!$firstParent instanceof \PhpParser\Node) {
            return \false;
        }
        foreach (self::LOOP_NODES as $type) {
            if (\is_a($firstParent, $type, \true)) {
                return \true;
            }
        }
        return \false;
    }
    public function isInSwitch(\PhpParser\Node $node) : bool
    {
        return (bool) $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Switch_::class);
    }
    public function isInIf(\PhpParser\Node $node) : bool
    {
        $breakNodes = \array_merge([\PhpParser\Node\Stmt\If_::class], self::BREAK_NODES);
        $previousNode = $this->betterNodeFinder->findParentByTypes($node, $breakNodes);
        if (!$previousNode instanceof \PhpParser\Node) {
            return \false;
        }
        return $previousNode instanceof \PhpParser\Node\Stmt\If_;
    }
    public function isHasAssignWithIndirectReturn(\PhpParser\Node $node, \PhpParser\Node\Stmt\If_ $if) : bool
    {
        $loopNodes = self::LOOP_NODES;
        foreach ($loopNodes as $loopNode) {
            $loopObjectType = new \PHPStan\Type\ObjectType($loopNode);
            $parentType = $this->nodeTypeResolver->getType($node);
            $superType = $parentType->isSuperTypeOf($loopObjectType);
            $isLoopType = $superType->yes();
            if (!$isLoopType) {
                continue;
            }
            $next = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
            if ($next instanceof \PhpParser\Node) {
                if ($next instanceof \PhpParser\Node\Stmt\Return_ && $next->expr === null) {
                    continue;
                }
                $hasAssign = (bool) $this->betterNodeFinder->findInstanceOf($if->stmts, \PhpParser\Node\Expr\Assign::class);
                if (!$hasAssign) {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
}
