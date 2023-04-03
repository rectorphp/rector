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
final class RemoveUnusedVariableAssignRector extends AbstractRector
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
    public function __construct(ReservedKeywordAnalyzer $reservedKeywordAnalyzer, ConditionSearcher $conditionSearcher, UsedVariableNameAnalyzer $usedVariableNameAnalyzer, SideEffectNodeDetector $sideEffectNodeDetector, ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer, FollowedByCurlyBracketAnalyzer $followedByCurlyBracketAnalyzer)
    {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->conditionSearcher = $conditionSearcher;
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->exprUsedInNextNodeAnalyzer = $exprUsedInNextNodeAnalyzer;
        $this->followedByCurlyBracketAnalyzer = $followedByCurlyBracketAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused assigns to variables', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $variable = $node->var;
        if (!$variable instanceof Variable) {
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
        return null;
    }
    private function cleanCastedExpr(Expr $expr) : Expr
    {
        if (!$expr instanceof Cast) {
            return $expr;
        }
        $castedExpr = $expr->expr;
        return $this->cleanCastedExpr($castedExpr);
    }
    private function hasCallLikeInAssignExpr(Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($expr, function (Node $subNode) : bool {
            return $this->sideEffectNodeDetector->detectCallExpr($subNode);
        });
    }
    private function shouldSkip(Assign $assign) : bool
    {
        $classMethod = $this->betterNodeFinder->findParentType($assign, ClassMethod::class);
        if (!$classMethod instanceof FunctionLike) {
            return \true;
        }
        $variable = $assign->var;
        if (!$variable instanceof Variable) {
            return \true;
        }
        $parentNode = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Expression) {
            return \true;
        }
        $originalNode = $parentNode->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalNode instanceof Node) {
            return \true;
        }
        if (!$variable->name instanceof Variable) {
            return $this->followedByCurlyBracketAnalyzer->isFollowed($this->file, $variable);
        }
        return (bool) $this->betterNodeFinder->findFirstNext($assign, static function (Node $node) : bool {
            return $node instanceof Variable;
        });
    }
    private function isUsed(Assign $assign, Variable $variable) : bool
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
    private function isUsedInPreviousNode(Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($variable, function (Node $node) use($variable) : bool {
            return $this->usedVariableNameAnalyzer->isVariableNamed($node, $variable);
        });
    }
    /**
     * @param \PhpParser\Node\Expr\CallLike|\PhpParser\Node\Expr $expr
     */
    private function isUsedInAssignExpr($expr, Assign $assign) : bool
    {
        if (!$expr instanceof CallLike) {
            return $this->isUsedInPreviousAssign($assign, $expr);
        }
        if ($expr->isFirstClassCallable()) {
            return \false;
        }
        foreach ($expr->getArgs() as $arg) {
            $variable = $arg->value;
            if ($this->isUsedInPreviousAssign($assign, $variable)) {
                return \true;
            }
        }
        return \false;
    }
    private function isUsedInPreviousAssign(Assign $assign, Expr $expr) : bool
    {
        if (!$expr instanceof Variable) {
            return \false;
        }
        $previousAssign = $this->betterNodeFinder->findFirstPrevious($assign, function (Node $node) use($expr) : bool {
            return $node instanceof Assign && $this->usedVariableNameAnalyzer->isVariableNamed($node->var, $expr);
        });
        if ($previousAssign instanceof Assign) {
            return $this->isUsed($assign, $expr);
        }
        return \false;
    }
    private function refactorUsedVariable(Assign $assign) : ?\PhpParser\Node\Expr
    {
        $parentNode = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Expression) {
            return null;
        }
        $if = $parentNode->getAttribute(AttributeKey::NEXT_NODE);
        // check if next node is if
        if (!$if instanceof If_) {
            if ($assign->var instanceof Variable && !$this->isUsedInPreviousNode($assign->var) && !$this->exprUsedInNextNodeAnalyzer->isUsed($assign->var)) {
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
