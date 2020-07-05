<?php

declare(strict_types=1);

namespace Rector\Core\Context;

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

        $firstParent = $this->betterNodeFinder->findFirstParentInstanceOf($node, $stopNodes);
        if ($firstParent === null) {
            return false;
        }

        return $this->isTypes($firstParent, self::LOOP_NODES);
    }

    public function isInIf(Node $node): bool
    {
        $breakNodes = array_merge([If_::class], self::BREAK_NODES);

        $previousNode = $this->betterNodeFinder->findFirstParentInstanceOf($node, $breakNodes);

        if ($previousNode === null) {
            return false;
        }

        return $this->isTypes($previousNode, [If_::class]);
    }

    /**
     * @param string[] $types
     */
    private function isTypes(Node $node, array $types): bool
    {
        foreach ($types as $type) {
            if (is_a($node, $type, true)) {
                return true;
            }
        }

        return false;
    }
}
