<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\PhpParser\Comparing\ConditionSearcher;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
 */
final class RemoveUnusedVariableAssignRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\Php\ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;
    /**
     * @var \Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer
     */
    private $compactFuncCallAnalyzer;
    /**
     * @var \Rector\Core\PhpParser\Comparing\ConditionSearcher
     */
    private $conditionSearcher;
    /**
     * @var \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer
     */
    private $usedVariableNameAnalyzer;
    public function __construct(\Rector\Core\Php\ReservedKeywordAnalyzer $reservedKeywordAnalyzer, \Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer $compactFuncCallAnalyzer, \Rector\Core\PhpParser\Comparing\ConditionSearcher $conditionSearcher, \Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer $usedVariableNameAnalyzer)
    {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
        $this->conditionSearcher = $conditionSearcher;
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
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
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\MethodCall || $node->expr instanceof \PhpParser\Node\Expr\StaticCall) {
            // keep the expr, can have side effect
            return $node->expr;
        }
        $this->removeNode($node);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        $classMethod = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\FunctionLike) {
            return \true;
        }
        $variable = $assign->var;
        if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
            return \true;
        }
        return $variable->name instanceof \PhpParser\Node\Expr\Variable && $this->betterNodeFinder->findFirstNext($assign, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Expr\Variable;
        });
    }
    private function isUsed(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        $isUsedPrev = (bool) $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (\PhpParser\Node $node) use($variable) : bool {
            return $this->usedVariableNameAnalyzer->isVariableNamed($node, $variable);
        });
        if ($isUsedPrev) {
            return \true;
        }
        if ($this->isUsedNext($variable)) {
            return \true;
        }
        /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $expr */
        $expr = $assign->expr;
        if (!$this->isCall($expr)) {
            return \false;
        }
        return $this->isUsedInAssignExpr($expr, $assign);
    }
    private function isUsedNext(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext($variable, function (\PhpParser\Node $node) use($variable) : bool {
            if ($this->usedVariableNameAnalyzer->isVariableNamed($node, $variable)) {
                return \true;
            }
            if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
                return $this->compactFuncCallAnalyzer->isInCompact($node, $variable);
            }
            return $node instanceof \PhpParser\Node\Expr\Include_;
        });
    }
    /**
     * @param FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $expr
     */
    private function isUsedInAssignExpr(\PhpParser\Node\Expr $expr, \PhpParser\Node\Expr\Assign $assign) : bool
    {
        foreach ($expr->args as $arg) {
            $variable = $arg->value;
            if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            $previousAssign = $this->betterNodeFinder->findFirstPreviousOfNode($assign, function (\PhpParser\Node $node) use($variable) : bool {
                return $node instanceof \PhpParser\Node\Expr\Assign && $this->usedVariableNameAnalyzer->isVariableNamed($node->var, $variable);
            });
            if ($previousAssign instanceof \PhpParser\Node\Expr\Assign) {
                return $this->isUsed($assign, $variable);
            }
        }
        return \false;
    }
    private function isCall(\PhpParser\Node\Expr $expr) : bool
    {
        return $expr instanceof \PhpParser\Node\Expr\FuncCall || $expr instanceof \PhpParser\Node\Expr\MethodCall || $expr instanceof \PhpParser\Node\Expr\New_ || $expr instanceof \PhpParser\Node\Expr\NullsafeMethodCall || $expr instanceof \PhpParser\Node\Expr\StaticCall;
    }
    private function refactorUsedVariable(\PhpParser\Node\Expr\Assign $assign) : ?\PhpParser\Node\Expr\Assign
    {
        $parentNode = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $if = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        // check if next node is if
        if (!$if instanceof \PhpParser\Node\Stmt\If_) {
            return null;
        }
        if ($this->conditionSearcher->searchIfAndElseForVariableRedeclaration($assign, $if)) {
            $this->removeNode($assign);
            return $assign;
        }
        return null;
    }
}
