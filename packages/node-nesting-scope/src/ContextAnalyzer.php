<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\StaticInstanceOf;

final class ContextAnalyzer
{
    /**
     * Nodes that break the scope they way up, e.g. class method
     * @var string[]
     */
    private const BREAK_NODES = [FunctionLike::class, ClassMethod::class];

    /**
     * @var string[]
     */
    private const LOOP_NODES = [For_::class, Foreach_::class, While_::class, Do_::class, Switch_::class];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function isInLoop(Node $node): bool
    {
        $stopNodes = array_merge(self::LOOP_NODES, self::BREAK_NODES);

        $firstParent = $this->betterNodeFinder->findParentTypes($node, $stopNodes);
        if (! $firstParent instanceof Node) {
            return false;
        }

        return StaticInstanceOf::isOneOf($firstParent, self::LOOP_NODES);
    }

    public function isInIf(Node $node): bool
    {
        $breakNodes = array_merge([If_::class], self::BREAK_NODES);

        $previousNode = $this->betterNodeFinder->findParentTypes($node, $breakNodes);

        if (! $previousNode instanceof Node) {
            return false;
        }

        return $previousNode instanceof If_;
    }
}
