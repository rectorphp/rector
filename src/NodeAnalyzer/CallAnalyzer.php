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
use RectorPrefix20211231\Symfony\Contracts\Service\Attribute\Required;
final class CallAnalyzer
{
    /**
     * @var array<class-string<Expr>>
     */
    private const OBJECT_CALL_TYPES = [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\NullsafeMethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @required
     */
    public function autowire(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder) : void
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isObjectCall(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\BooleanNot) {
            $expr = $expr->expr;
        }
        if ($expr instanceof \PhpParser\Node\Expr\BinaryOp) {
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
    public function isNewInstance(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\Clone_ || $expr instanceof \PhpParser\Node\Expr\New_) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($expr, function (\PhpParser\Node $node) use($expr) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            if (!$this->nodeComparator->areNodesEqual($node->var, $expr)) {
                return \false;
            }
            return $node->expr instanceof \PhpParser\Node\Expr\Clone_ || $node->expr instanceof \PhpParser\Node\Expr\New_;
        });
    }
}
