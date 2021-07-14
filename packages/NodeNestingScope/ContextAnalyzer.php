<?php

declare(strict_types=1);

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
    private const BREAK_NODES = [FunctionLike::class, ClassMethod::class];

    /**
     * @var array<class-string<Stmt>>
     */
    private const LOOP_NODES = [For_::class, Foreach_::class, While_::class, Do_::class];

    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeTypeResolver $nodeTypeResolver,
        private ParentFinder $parentFinder
    ) {
    }

    public function isInLoop(Node $node): bool
    {
        $stopNodes = array_merge(self::LOOP_NODES, self::BREAK_NODES);

        $firstParent = $this->parentFinder->findByTypes($node, $stopNodes);
        if (! $firstParent instanceof Node) {
            return false;
        }

        foreach (self::LOOP_NODES as $type) {
            if (is_a($firstParent, $type, true)) {
                return true;
            }
        }

        return false;
    }

    public function isInSwitch(Node $node): bool
    {
        return (bool) $this->betterNodeFinder->findParentType($node, Switch_::class);
    }

    public function isInIf(Node $node): bool
    {
        $breakNodes = array_merge([If_::class], self::BREAK_NODES);

        $previousNode = $this->parentFinder->findByTypes($node, $breakNodes);

        if (! $previousNode instanceof Node) {
            return false;
        }

        return $previousNode instanceof If_;
    }

    public function isHasAssignWithIndirectReturn(Node $node, If_ $if): bool
    {
        $loopNodes = self::LOOP_NODES;

        foreach ($loopNodes as $loopNode) {
            $loopObjectType = new ObjectType($loopNode);
            $parentType = $this->nodeTypeResolver->resolve($node);
            $superType = $parentType->isSuperTypeOf($loopObjectType);
            $isLoopType = $superType->yes();

            if (! $isLoopType) {
                continue;
            }

            $next = $node->getAttribute(AttributeKey::NEXT_NODE);
            if ($next instanceof Node) {
                if ($next instanceof Return_ && $next->expr === null) {
                    continue;
                }

                $hasAssign = (bool) $this->betterNodeFinder->findInstanceOf($if->stmts, Assign::class);
                if (! $hasAssign) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }
}
