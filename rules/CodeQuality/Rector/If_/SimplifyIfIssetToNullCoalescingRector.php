<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector\SimplifyIfIssetToNullCoalescingRectorTest
 */
final class SimplifyIfIssetToNullCoalescingRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify binary if to null coalesce', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    public function run($possibleStatieYamlFile)
    {
        if (isset($possibleStatieYamlFile['import'])) {
            $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'], $filesToImport);
        } else {
            $possibleStatieYamlFile['import'] = $filesToImport;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    public function run($possibleStatieYamlFile)
    {
        $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'] ?? [], $filesToImport);
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var Isset_ $issetNode */
        $issetNode = $node->cond;
        $valueNode = $issetNode->vars[0];
        // various scenarios
        $ifFirstStmt = $node->stmts[0];
        if (!$ifFirstStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $else = $node->else;
        if (!$else instanceof \PhpParser\Node\Stmt\Else_) {
            return null;
        }
        $elseFirstStmt = $else->stmts[0];
        if (!$elseFirstStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        /** @var Assign $firstAssign */
        $firstAssign = $ifFirstStmt->expr;
        /** @var Assign $secondAssign */
        $secondAssign = $elseFirstStmt->expr;
        // 1. array_merge
        if (!$firstAssign->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->isName($firstAssign->expr, 'array_merge')) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($firstAssign->expr->args, [0, 1])) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = $firstAssign->expr->args[0];
        if (!$this->nodeComparator->areNodesEqual($firstArg->value, $valueNode)) {
            return null;
        }
        /** @var Arg $secondArg */
        $secondArg = $firstAssign->expr->args[1];
        if (!$this->nodeComparator->areNodesEqual($secondAssign->expr, $secondArg->value)) {
            return null;
        }
        $args = [new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\BinaryOp\Coalesce($valueNode, new \PhpParser\Node\Expr\Array_([]))), new \PhpParser\Node\Arg($secondAssign->expr)];
        $funcCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('array_merge'), $args);
        return new \PhpParser\Node\Expr\Assign($valueNode, $funcCall);
    }
    private function shouldSkip(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        if ($if->else === null) {
            return \true;
        }
        if (\count($if->elseifs) > 1) {
            return \true;
        }
        if (!$if->cond instanceof \PhpParser\Node\Expr\Isset_) {
            return \true;
        }
        if (!$this->hasOnlyStatementAssign($if)) {
            return \true;
        }
        if (!$this->hasOnlyStatementAssign($if->else)) {
            return \true;
        }
        $ifStmt = $if->stmts[0];
        if (!$ifStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return \true;
        }
        if (!$ifStmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return \true;
        }
        if (!$this->nodeComparator->areNodesEqual($if->cond->vars[0], $ifStmt->expr->var)) {
            return \true;
        }
        $firstElseStmt = $if->else->stmts[0];
        if (!$firstElseStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return \false;
        }
        if (!$firstElseStmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        return !$this->nodeComparator->areNodesEqual($if->cond->vars[0], $firstElseStmt->expr->var);
    }
    /**
     * @param \PhpParser\Node\Stmt\Else_|\PhpParser\Node\Stmt\If_ $node
     */
    private function hasOnlyStatementAssign($node) : bool
    {
        if (\count($node->stmts) !== 1) {
            return \false;
        }
        if (!$node->stmts[0] instanceof \PhpParser\Node\Stmt\Expression) {
            return \false;
        }
        return $node->stmts[0]->expr instanceof \PhpParser\Node\Expr\Assign;
    }
}
