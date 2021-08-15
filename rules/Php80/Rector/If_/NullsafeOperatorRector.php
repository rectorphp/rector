<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\NodeManipulator\NullsafeManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/nullsafe_operator
 * @see \Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\NullsafeOperatorRectorTest
 */
final class NullsafeOperatorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const NAME = 'name';
    /**
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @var \Rector\Core\NodeManipulator\NullsafeManipulator
     */
    private $nullsafeManipulator;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\Core\NodeManipulator\NullsafeManipulator $nullsafeManipulator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->nullsafeManipulator = $nullsafeManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change if null check with nullsafe operator ?-> with full short circuiting', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($someObject)
    {
        $someObject2 = $someObject->mayFail1();
        if ($someObject2 === null) {
            return null;
        }

        return $someObject2->mayFail2();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($someObject)
    {
        return $someObject->mayFail1()?->mayFail2();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Expr\Ternary::class];
    }
    /**
     * @param If_|Ternary $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\If_) {
            return $this->handleIfNode($node);
        }
        return $this->handleTernaryNode($node);
    }
    private function handleIfNode(\PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node
    {
        $processNullSafeOperator = $this->processNullSafeOperatorIdentical($if);
        if ($processNullSafeOperator !== null) {
            /** @var Expression $prevNode */
            $prevNode = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
            $this->removeNode($prevNode);
            return $processNullSafeOperator;
        }
        return $this->processNullSafeOperatorNotIdentical($if);
    }
    private function processNullSafeOperatorIdentical(\PhpParser\Node\Stmt\If_ $if, bool $isStartIf = \true) : ?\PhpParser\Node
    {
        $comparedNode = $this->ifManipulator->matchIfValueReturnValue($if);
        if (!$comparedNode instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $prevNode = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        $nextNode = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node) {
            return null;
        }
        if (!$prevNode instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $prevExpr = $prevNode->expr;
        if (!$prevExpr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        if (!$this->ifManipulator->isIfCondUsingAssignIdenticalVariable($if, $prevExpr)) {
            return null;
        }
        return $this->processAssign($prevExpr, $prevNode, $nextNode, $isStartIf);
    }
    private function processNullSafeOperatorNotIdentical(\PhpParser\Node\Stmt\If_ $if, ?\PhpParser\Node\Expr $expr = null) : ?\PhpParser\Node
    {
        $assign = $this->ifManipulator->matchIfNotNullNextAssignment($if);
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $assignExpr = $assign->expr;
        if ($this->ifManipulator->isIfCondUsingAssignNotIdenticalVariable($if, $assignExpr)) {
            return null;
        }
        $expression = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$expression instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $nextNode = $expression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        $nullSafe = $this->nullsafeManipulator->processNullSafeExpr($assignExpr);
        if ($nullSafe === null) {
            return null;
        }
        if ($expr !== null) {
            /** @var Identifier $nullSafeIdentifier */
            $nullSafeIdentifier = $nullSafe->name;
            /** @var NullsafeMethodCall|NullsafePropertyFetch $nullSafe */
            $nullSafe = $this->nullsafeManipulator->processNullSafeExprResult($expr, $nullSafeIdentifier);
        }
        $nextOfNextNode = $this->processIfMayInNextNode($nextNode);
        if ($nextOfNextNode !== null) {
            return $nextOfNextNode;
        }
        if (!$nextNode instanceof \PhpParser\Node\Stmt\If_) {
            $nullSafe = $this->verifyDefaultValueInElse($if, $nullSafe, $assign);
            if ($nullSafe === null) {
                return null;
            }
            return new \PhpParser\Node\Expr\Assign($assign->var, $nullSafe);
        }
        return $this->processNullSafeOperatorNotIdentical($nextNode, $nullSafe);
    }
    /**
     * @param \PhpParser\Node\Expr\NullsafeMethodCall|\PhpParser\Node\Expr\NullsafePropertyFetch $nullSafe
     * @return \PhpParser\Node\Expr\NullsafeMethodCall|\PhpParser\Node\Expr\NullsafePropertyFetch|\PhpParser\Node\Expr\BinaryOp\Coalesce|null
     */
    private function verifyDefaultValueInElse(\PhpParser\Node\Stmt\If_ $if, $nullSafe, \PhpParser\Node\Expr\Assign $assign)
    {
        if (!$if->else instanceof \PhpParser\Node\Stmt\Else_) {
            return $nullSafe;
        }
        if (\count($if->else->stmts) !== 1) {
            return null;
        }
        $expression = $if->else->stmts[0];
        if (!$expression instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $expressionAssign = $expression->expr;
        if (!$expressionAssign instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($expressionAssign->var, $assign->var)) {
            return null;
        }
        if ($this->valueResolver->isNull($expressionAssign->expr)) {
            return $nullSafe;
        }
        return new \PhpParser\Node\Expr\BinaryOp\Coalesce($nullSafe, $expressionAssign->expr);
    }
    private function processAssign(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Stmt\Expression $prevExpression, \PhpParser\Node $nextNode, bool $isStartIf) : ?\PhpParser\Node
    {
        if ($this->shouldProcessAssignInCurrentNode($assign, $nextNode)) {
            return $this->processAssignInCurrentNode($assign, $prevExpression, $nextNode, $isStartIf);
        }
        return $this->processAssignMayInNextNode($nextNode);
    }
    private function shouldProcessAssignInCurrentNode(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node $nextNode) : bool
    {
        if (!\property_exists($assign->expr, self::NAME)) {
            return \false;
        }
        if (!\property_exists($nextNode, 'expr')) {
            return \false;
        }
        if (!\property_exists($nextNode->expr, self::NAME)) {
            return \false;
        }
        return !$this->valueResolver->isNull($nextNode->expr);
    }
    private function processIfMayInNextNode(?\PhpParser\Node $nextNode = null) : ?\PhpParser\Node
    {
        if (!$nextNode instanceof \PhpParser\Node) {
            return null;
        }
        $nextOfNextNode = $nextNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        while ($nextOfNextNode) {
            if ($nextOfNextNode instanceof \PhpParser\Node\Stmt\If_) {
                /** @var If_ $beforeIf */
                $beforeIf = $nextOfNextNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
                $nullSafe = $this->processNullSafeOperatorNotIdentical($nextOfNextNode);
                if (!$nullSafe instanceof \PhpParser\Node\Expr\NullsafeMethodCall && !$nullSafe instanceof \PhpParser\Node\Expr\PropertyFetch) {
                    return $beforeIf;
                }
                $beforeIf->stmts[\count($beforeIf->stmts) - 1] = new \PhpParser\Node\Stmt\Expression($nullSafe);
                return $beforeIf;
            }
            $nextOfNextNode = $nextOfNextNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        }
        return null;
    }
    private function processAssignInCurrentNode(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node $nextNode, bool $isStartIf) : ?\PhpParser\Node
    {
        $assignNullSafe = $isStartIf ? $assign->expr : $this->nullsafeManipulator->processNullSafeExpr($assign->expr);
        $nullSafe = $this->nullsafeManipulator->processNullSafeExprResult($assignNullSafe, $nextNode->expr->name);
        $prevAssign = $expression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if ($prevAssign instanceof \PhpParser\Node\Stmt\If_) {
            $nullSafe = $this->getNullSafeOnPrevAssignIsIf($prevAssign, $nextNode, $nullSafe);
        }
        $this->removeNode($nextNode);
        if ($nextNode instanceof \PhpParser\Node\Stmt\Return_) {
            $nextNode->expr = $nullSafe;
            return $nextNode;
        }
        return $nullSafe;
    }
    private function processAssignMayInNextNode(\PhpParser\Node $nextNode) : ?\PhpParser\Node
    {
        if (!$nextNode instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if (!$nextNode->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $mayNextIf = $nextNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$mayNextIf instanceof \PhpParser\Node\Stmt\If_) {
            return null;
        }
        if ($this->ifManipulator->isIfCondUsingAssignIdenticalVariable($mayNextIf, $nextNode->expr)) {
            return $this->processNullSafeOperatorIdentical($mayNextIf, \false);
        }
        return null;
    }
    private function getNullSafeOnPrevAssignIsIf(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node $nextNode, ?\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr
    {
        $prevIf = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if (!$prevIf instanceof \PhpParser\Node\Stmt\Expression) {
            return $expr;
        }
        if (!$this->ifManipulator->isIfCondUsingAssignIdenticalVariable($if, $prevIf->expr)) {
            return $expr;
        }
        $start = $prevIf;
        while ($prevIf instanceof \PhpParser\Node\Stmt\Expression) {
            $expressionNode = $prevIf->expr;
            if (!$expressionNode instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            $expr = $this->nullsafeManipulator->processNullSafeExpr($expressionNode->expr);
            /** @var Node $prevPrevIf */
            $prevPrevIf = $prevIf->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
            /** @var Node $prevPrevPrevIf */
            $prevPrevPrevIf = $prevPrevIf->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
            if (!$prevPrevPrevIf instanceof \PhpParser\Node\Stmt\Expression && $prevPrevPrevIf !== null) {
                $start = $this->getPreviousIf($prevPrevPrevIf);
                break;
            }
            $prevIf = $prevPrevPrevIf;
        }
        if (!$expr instanceof \PhpParser\Node\Expr\NullsafeMethodCall && !$expr instanceof \PhpParser\Node\Expr\NullsafePropertyFetch) {
            return $expr;
        }
        /** @var Expr $expr */
        $expr = $expr->var->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $expr = $this->getNullSafeAfterStartUntilBeforeEnd($start, $expr);
        return $this->nullsafeManipulator->processNullSafeExprResult($expr, $nextNode->expr->name);
    }
    private function getPreviousIf(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        /** @var If_ $if */
        $if = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        /** @var Expression $expression */
        $expression = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        /** @var Expression $nextExpression */
        $nextExpression = $expression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        return $nextExpression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
    }
    private function getNullSafeAfterStartUntilBeforeEnd(?\PhpParser\Node $node, ?\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr
    {
        while ($node) {
            $expr = $this->nullsafeManipulator->processNullSafeExprResult($expr, $node->expr->expr->name);
            $node = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
            while ($node) {
                /** @var If_ $if */
                $if = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
                if ($node instanceof \PhpParser\Node\Stmt\Expression && $this->ifManipulator->isIfCondUsingAssignIdenticalVariable($if, $node->expr)) {
                    break;
                }
                $node = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
            }
        }
        return $expr;
    }
    private function handleTernaryNode(\PhpParser\Node\Expr\Ternary $ternary) : ?\PhpParser\Node
    {
        if ($this->shouldSkipTernary($ternary)) {
            return null;
        }
        $nullSafeElse = $this->nullsafeManipulator->processNullSafeExpr($ternary->else);
        if ($nullSafeElse !== null) {
            return $nullSafeElse;
        }
        if ($ternary->if === null) {
            return null;
        }
        return $this->nullsafeManipulator->processNullSafeExpr($ternary->if);
    }
    private function shouldSkipTernary(\PhpParser\Node\Expr\Ternary $ternary) : bool
    {
        if (!$this->canTernaryReturnNull($ternary)) {
            return \true;
        }
        if ($ternary->cond instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return !$this->hasNullComparison($ternary->cond);
        }
        if ($ternary->cond instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) {
            return !$this->hasNullComparison($ternary->cond);
        }
        return \true;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\Identical $check
     */
    private function hasNullComparison($check) : bool
    {
        if ($this->valueResolver->isNull($check->left)) {
            return \true;
        }
        return $this->valueResolver->isNull($check->right);
    }
    private function canTernaryReturnNull(\PhpParser\Node\Expr\Ternary $ternary) : bool
    {
        if ($this->valueResolver->isNull($ternary->else)) {
            return \true;
        }
        if ($ternary->if === null) {
            // $foo === null ?: 'xx' returns true if $foo is null
            // therefore it does not return null in case of the elvis operator
            return \false;
        }
        return $this->valueResolver->isNull($ternary->if);
    }
}
