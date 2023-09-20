<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\SimplifyConditionsRector\SimplifyConditionsRectorTest
 */
final class SimplifyConditionsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\AssignAndBinaryMap
     */
    private $assignAndBinaryMap;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(AssignAndBinaryMap $assignAndBinaryMap, BinaryOpManipulator $binaryOpManipulator, ValueResolver $valueResolver)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify conditions', [new CodeSample("if (! (\$foo !== 'bar')) {...", "if (\$foo === 'bar') {...")]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [BooleanNot::class, Identical::class];
    }
    /**
     * @param BooleanNot|Identical $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof BooleanNot) {
            return $this->processBooleanNot($node);
        }
        return $this->processIdenticalAndNotIdentical($node);
    }
    private function processBooleanNot(BooleanNot $booleanNot) : ?Node
    {
        if (!$booleanNot->expr instanceof BinaryOp) {
            return null;
        }
        if ($this->shouldSkip($booleanNot->expr)) {
            return null;
        }
        return $this->createInversedBooleanOp($booleanNot->expr);
    }
    private function processIdenticalAndNotIdentical(Identical $identical) : ?Node
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($identical, static function (Node $node) : bool {
            return $node instanceof Identical || $node instanceof NotIdentical;
        }, function (Node $node) : bool {
            return $node instanceof Expr && $this->valueResolver->isTrueOrFalse($node);
        });
        if (!$twoNodeMatch instanceof TwoNodeMatch) {
            return $twoNodeMatch;
        }
        /** @var Identical|NotIdentical $firstExpr */
        $firstExpr = $twoNodeMatch->getFirstExpr();
        $otherExpr = $twoNodeMatch->getSecondExpr();
        if ($this->valueResolver->isFalse($otherExpr)) {
            return $this->createInversedBooleanOp($firstExpr);
        }
        return $firstExpr;
    }
    /**
     * Skip too nested binary || binary > binary combinations
     */
    private function shouldSkip(BinaryOp $binaryOp) : bool
    {
        if ($binaryOp instanceof BooleanOr) {
            return \true;
        }
        if ($binaryOp->left instanceof BinaryOp) {
            return \true;
        }
        return $binaryOp->right instanceof BinaryOp;
    }
    private function createInversedBooleanOp(BinaryOp $binaryOp) : ?BinaryOp
    {
        $inversedBinaryClass = $this->assignAndBinaryMap->getInversed($binaryOp);
        if ($inversedBinaryClass === null) {
            return null;
        }
        return new $inversedBinaryClass($binaryOp->left, $binaryOp->right);
    }
}
