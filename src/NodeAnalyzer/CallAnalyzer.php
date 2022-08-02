<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
final class CallAnalyzer
{
    /**
     * @var array<class-string<Expr>>
     */
    private const OBJECT_CALL_TYPES = [MethodCall::class, NullsafeMethodCall::class, StaticCall::class];
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @required
     */
    public function autowire(BetterNodeFinder $betterNodeFinder) : void
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isObjectCall(Expr $expr) : bool
    {
        if ($expr instanceof BooleanNot) {
            $expr = $expr->expr;
        }
        if ($expr instanceof BinaryOp) {
            $isObjectCallLeft = $this->isObjectCall($expr->left);
            $isObjectCallRight = $this->isObjectCall($expr->right);
            return $isObjectCallLeft || $isObjectCallRight;
        }
        foreach (self::OBJECT_CALL_TYPES as $objectCallType) {
            if (\is_a($expr, $objectCallType, \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param If_[] $ifs
     */
    public function doesIfHasObjectCall(array $ifs) : bool
    {
        foreach ($ifs as $if) {
            if ($this->isObjectCall($if->cond)) {
                return \true;
            }
        }
        return \false;
    }
    public function isNewInstance(Expr $expr) : bool
    {
        if ($expr instanceof Clone_ || $expr instanceof New_) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirstPrevious($expr, function (Node $node) use($expr) : bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            if (!$this->nodeComparator->areNodesEqual($node->var, $expr)) {
                return \false;
            }
            return $node->expr instanceof Clone_ || $node->expr instanceof New_;
        });
    }
}
