<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\SimplifyConditionsRector\SimplifyConditionsRectorTest
 */
final class SimplifyConditionsRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Core\PhpParser\Node\AssignAndBinaryMap $assignAndBinaryMap, \Rector\Core\NodeManipulator\BinaryOpManipulator $binaryOpManipulator)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify conditions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample("if (! (\$foo !== 'bar')) {...", "if (\$foo === 'bar') {...")]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BooleanNot::class, \PhpParser\Node\Expr\BinaryOp\Identical::class];
    }
    /**
     * @param BooleanNot|Identical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\BooleanNot) {
            return $this->processBooleanNot($node);
        }
        return $this->processIdenticalAndNotIdentical($node);
    }
    private function processBooleanNot(\PhpParser\Node\Expr\BooleanNot $booleanNot) : ?\PhpParser\Node
    {
        if (!$booleanNot->expr instanceof \PhpParser\Node\Expr\BinaryOp) {
            return null;
        }
        if ($this->shouldSkip($booleanNot->expr)) {
            return null;
        }
        return $this->createInversedBooleanOp($booleanNot->expr);
    }
    private function processIdenticalAndNotIdentical(\PhpParser\Node\Expr\BinaryOp\Identical $identical) : ?\PhpParser\Node
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($identical, function (\PhpParser\Node $binaryOp) : bool {
            return $binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\Identical || $binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical;
        }, function (\PhpParser\Node $binaryOp) : bool {
            return $this->valueResolver->isTrueOrFalse($binaryOp);
        });
        if (!$twoNodeMatch instanceof \Rector\Php71\ValueObject\TwoNodeMatch) {
            return $twoNodeMatch;
        }
        /** @var Identical|NotIdentical $subBinaryOp */
        $subBinaryOp = $twoNodeMatch->getFirstExpr();
        $otherNode = $twoNodeMatch->getSecondExpr();
        if ($this->valueResolver->isFalse($otherNode)) {
            return $this->createInversedBooleanOp($subBinaryOp);
        }
        return $subBinaryOp;
    }
    /**
     * Skip too nested binary || binary > binary combinations
     */
    private function shouldSkip(\PhpParser\Node\Expr\BinaryOp $binaryOp) : bool
    {
        if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return \true;
        }
        if ($binaryOp->left instanceof \PhpParser\Node\Expr\BinaryOp) {
            return \true;
        }
        return $binaryOp->right instanceof \PhpParser\Node\Expr\BinaryOp;
    }
    private function createInversedBooleanOp(\PhpParser\Node\Expr\BinaryOp $binaryOp) : ?\PhpParser\Node\Expr\BinaryOp
    {
        $inversedBinaryClass = $this->assignAndBinaryMap->getInversed($binaryOp);
        if ($inversedBinaryClass === null) {
            return null;
        }
        return new $inversedBinaryClass($binaryOp->left, $binaryOp->right);
    }
}
