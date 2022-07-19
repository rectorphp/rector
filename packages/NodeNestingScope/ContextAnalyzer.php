<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNestingScope\ValueObject\ControlStructure;
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
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isInLoop(Node $node) : bool
    {
        $stopNodes = \array_merge(ControlStructure::LOOP_NODES, self::BREAK_NODES);
        $firstParent = $this->betterNodeFinder->findParentByTypes($node, $stopNodes);
        if (!$firstParent instanceof Node) {
            return \false;
        }
        foreach (ControlStructure::LOOP_NODES as $type) {
            if (\is_a($firstParent, $type, \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @api
     */
    public function isInSwitch(Node $node) : bool
    {
        return (bool) $this->betterNodeFinder->findParentType($node, Switch_::class);
    }
    /**
     * @api
     */
    public function isInIf(Node $node) : bool
    {
        $breakNodes = \array_merge([If_::class], self::BREAK_NODES);
        $previousNode = $this->betterNodeFinder->findParentByTypes($node, $breakNodes);
        if (!$previousNode instanceof Node) {
            return \false;
        }
        return $previousNode instanceof If_;
    }
    public function hasAssignWithIndirectReturn(Node $node, If_ $if) : bool
    {
        foreach (ControlStructure::LOOP_NODES as $loopNode) {
            $loopObjectType = new ObjectType($loopNode);
            $parentType = $this->nodeTypeResolver->getType($node);
            $superType = $parentType->isSuperTypeOf($loopObjectType);
            if (!$superType->yes()) {
                continue;
            }
            $next = $node->getAttribute(AttributeKey::NEXT_NODE);
            if ($next instanceof Node) {
                if ($next instanceof Return_ && $next->expr === null) {
                    continue;
                }
                $hasAssign = (bool) $this->betterNodeFinder->findInstanceOf($if->stmts, Assign::class);
                if (!$hasAssign) {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
}
