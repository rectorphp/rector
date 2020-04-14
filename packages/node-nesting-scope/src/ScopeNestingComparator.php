<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class ScopeNestingComparator
{
    /**
     * @var class-string[]
     */
    private const CONTROL_STRUCTURE_NODES = [
        For_::class,
        Foreach_::class,
        If_::class,
        While_::class,
        Do_::class,
        Else_::class,
        ElseIf_::class,
        Catch_::class,
        Case_::class,
        FunctionLike::class,
    ];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function areScopeNestingEqual(Node $firstNode, Node $secondNode): bool
    {
        $firstNodeScopeNode = $this->findParentControlStructure($firstNode);
        $secondNodeScopeNode = $this->findParentControlStructure($secondNode);

        return $firstNodeScopeNode === $secondNodeScopeNode;
    }

    private function findParentControlStructure(Node $node): ?Node
    {
        return $this->betterNodeFinder->findFirstParentInstanceOf($node, self::CONTROL_STRUCTURE_NODES);
    }
}
