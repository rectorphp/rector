<?php declare(strict_types=1);

namespace Rector\Context;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\While_;
use Rector\PhpParser\Node\BetterNodeFinder;

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
    private const LOOP_NODES = [For_::class, Foreach_::class, While_::class, Do_::class, Node\Stmt\Switch_::class];

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
        $firstParent = $this->betterNodeFinder->findFirstParentInstanceOf(
            $node,
            array_merge(self::LOOP_NODES, self::BREAK_NODES)
        );

        if ($firstParent === null) {
            return false;
        }

        return $this->isTypes($firstParent, self::LOOP_NODES);
    }

    public function isInIf(Node\Stmt\Break_ $node): bool
    {
        $previousNode = $this->betterNodeFinder->findFirstParentInstanceOf(
            $node,
            array_merge([Node\Stmt\If_::class], self::BREAK_NODES)
        );

        if ($previousNode === null) {
            return false;
        }

        return $this->isTypes($previousNode, [Node\Stmt\If_::class]);
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
