<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\PhpParser\Comparing\ConditionSearcher;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer;
use Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php74\Tokenizer\FollowedByCurlyBracketAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
 */
final class RemoveUnusedVariableAssignRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Php\ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\ConditionSearcher
     */
    private $conditionSearcher;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer
     */
    private $usedVariableNameAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer
     */
    private $exprUsedInNextNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php74\Tokenizer\FollowedByCurlyBracketAnalyzer
     */
    private $followedByCurlyBracketAnalyzer;
    public function __construct(\Rector\Core\Php\ReservedKeywordAnalyzer $reservedKeywordAnalyzer, \Rector\Core\PhpParser\Comparing\ConditionSearcher $conditionSearcher, \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer $usedVariableNameAnalyzer, \Rector\DeadCode\SideEffect\SideEffectNodeDetector $sideEffectNodeDetector, \Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer, \Rector\Php74\Tokenizer\FollowedByCurlyBracketAnalyzer $followedByCurlyBracketAnalyzer)
    {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->conditionSearcher = $conditionSearcher;
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->exprUsedInNextNodeAnalyzer = $exprUsedInNextNodeAnalyzer;
        $this->followedByCurlyBracketAnalyzer = $followedByCurlyBracketAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused assigns to variables', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
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
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $variable = $node->var;
        if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $variableName = $this->getName($variable);
        if ($variableName !== null && $this->reservedKeywordAnalyzer->isNativeVariable($variableName)) {
            return null;
        }
        // variable is used
        if ($this->isUsed($node, $variable)) {
            return $this->refactorUsedVariable($node);
        }
        if ($this->hasCallLikeInAssignExpr($node->expr)) {
            // keep the expr, can have side effect
            return $this->cleanCastedExpr($node->expr);
        }
        $this->removeNode($node);
        return $node;
    }
    private function cleanCastedExpr(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Cast) {
            return $expr;
        }
        $castedExpr = $expr->expr;
        return $this->cleanCastedExpr($castedExpr);
    }
    private function hasCallLikeInAssignExpr(\PhpParser\Node\Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($expr, function (\PhpParser\Node $subNode) : bool {
            return $this->sideEffectNodeDetector->detectCallExpr($subNode);
        });
    }
    private function shouldSkip(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        $classMethod = $this->betterNodeFinder->findParentType($assign, \PhpParser\Node\Stmt\ClassMethod::class);
        if (!$classMethod instanceof \PhpParser\Node\FunctionLike) {
            return \true;
        }
        $variable = $assign->var;
        if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
            return \true;
        }
        $parentNode = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Stmt\Expression) {
            return \true;
        }
        $originalNode = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
        if (!$originalNode instanceof \PhpParser\Node) {
            return \true;
        }
        if (!$variable->name instanceof \PhpParser\Node\Expr\Variable) {
            return $this->followedByCurlyBracketAnalyzer->isFollowed($this->file, $variable);
        }
        return (bool) $this->betterNodeFinder->findFirstNext($assign, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Expr\Variable;
        });
    }
    private function isUsed(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        $isUsedPrev = $this->isUsedInPreviousNode($variable);
        if ($isUsedPrev) {
            return \true;
        }
        if ($this->exprUsedInNextNodeAnalyzer->isUsed($variable)) {
            return \true;
        }
        /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $expr */
        $expr = $assign->expr;
        if (!$this->sideEffectNodeDetector->detectCallExpr($expr)) {
            return \false;
        }
        return $this->isUsedInAssignExpr($expr, $assign);
    }
    private function isUsedInPreviousNode(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (\PhpParser\Node $node) use($variable) : bool {
            return $this->usedVariableNameAnalyzer->isVariableNamed($node, $variable);
        });
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\CallLike $expr
     */
    private function isUsedInAssignExpr($expr, \PhpParser\Node\Expr\Assign $assign) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\CallLike) {
            return $this->isUsedInPreviousAssign($assign, $expr);
        }
        foreach ($expr->getArgs() as $arg) {
            $variable = $arg->value;
            if ($this->isUsedInPreviousAssign($assign, $variable)) {
                return \true;
            }
        }
        return \false;
    }
    private function isUsedInPreviousAssign(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        $previousAssign = $this->betterNodeFinder->findFirstPreviousOfNode($assign, function (\PhpParser\Node $node) use($expr) : bool {
            return $node instanceof \PhpParser\Node\Expr\Assign && $this->usedVariableNameAnalyzer->isVariableNamed($node->var, $expr);
        });
        if ($previousAssign instanceof \PhpParser\Node\Expr\Assign) {
            return $this->isUsed($assign, $expr);
        }
        return \false;
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    private function refactorUsedVariable(\PhpParser\Node\Expr\Assign $assign)
    {
        $parentNode = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $if = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        // check if next node is if
        if (!$if instanceof \PhpParser\Node\Stmt\If_) {
            if ($assign->var instanceof \PhpParser\Node\Expr\Variable && !$this->isUsedInPreviousNode($assign->var) && !$this->exprUsedInNextNodeAnalyzer->isUsed($assign->var)) {
                return $this->cleanCastedExpr($assign->expr);
            }
            return null;
        }
        if ($this->conditionSearcher->hasIfAndElseForVariableRedeclaration($assign, $if)) {
            $this->removeNode($assign);
            return $assign;
        }
        return null;
    }
}
