<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\UnionType;
use Rector\CodeQuality\NodeFactory\ArrayFilterFactory;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\SimplifyForeachToArrayFilterRectorTest
 */
final class SimplifyForeachToArrayFilterRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeFactory\ArrayFilterFactory
     */
    private $arrayFilterFactory;
    public function __construct(\Rector\CodeQuality\NodeFactory\ArrayFilterFactory $arrayFilterFactory)
    {
        $this->arrayFilterFactory = $arrayFilterFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify foreach with function filtering to array filter', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$directories = [];

$possibleDirectories = [];
foreach ($possibleDirectories as $possibleDirectory) {
    if (file_exists($possibleDirectory)) {
        $directories[] = $possibleDirectory;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$possibleDirectories = [];
$directories = array_filter($possibleDirectories, 'file_exists');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $foreachValueVar = $node->valueVar;
        if (!$foreachValueVar instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        /** @var If_ $ifNode */
        $ifNode = $node->stmts[0];
        $condExpr = $ifNode->cond;
        if ($condExpr instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->refactorFuncCall($ifNode, $condExpr, $node, $foreachValueVar);
        }
        $onlyStmt = $ifNode->stmts[0];
        if ($onlyStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return $this->refactorAssign($onlyStmt, $foreachValueVar, $node, $condExpr);
        }
        // another condition - not supported yet
        return null;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\Foreach_ $foreach) : bool
    {
        if (\count($foreach->stmts) !== 1) {
            return \true;
        }
        if (!$foreach->stmts[0] instanceof \PhpParser\Node\Stmt\If_) {
            return \true;
        }
        /** @var If_ $ifNode */
        $ifNode = $foreach->stmts[0];
        if ($ifNode->else !== null) {
            return \true;
        }
        return $ifNode->elseifs !== [];
    }
    private function isArrayDimFetchInForLoop(\PhpParser\Node\Stmt\Foreach_ $foreach, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        $loopVar = $foreach->expr;
        if (!$loopVar instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        $varThatIsModified = $arrayDimFetch->var;
        if (!$varThatIsModified instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $loopVar->name !== $varThatIsModified->name;
    }
    private function isSimpleFuncCallOnForeachedVariables(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\Variable $foreachVariable) : bool
    {
        if (\count($funcCall->args) !== 1) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($funcCall->args[0], $foreachVariable);
    }
    private function refactorFuncCall(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Stmt\Foreach_ $foreach, \PhpParser\Node\Expr\Variable $foreachVariable) : ?\PhpParser\Node\Expr\Assign
    {
        if (\count($if->stmts) !== 1) {
            return null;
        }
        if (!$this->isSimpleFuncCallOnForeachedVariables($funcCall, $foreachVariable)) {
            return null;
        }
        if (!$if->stmts[0] instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $onlyNodeInIf = $if->stmts[0]->expr;
        if (!$onlyNodeInIf instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $arrayDimFetch = $onlyNodeInIf->var;
        if (!$arrayDimFetch instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($onlyNodeInIf->expr, $foreachVariable)) {
            return null;
        }
        $funcName = $this->getName($funcCall);
        if ($funcName === null) {
            return null;
        }
        if (!$this->isArrayDimFetchInForLoop($foreach, $arrayDimFetch)) {
            return null;
        }
        return $this->arrayFilterFactory->createSimpleFuncCallAssign($foreach, $funcName, $arrayDimFetch);
    }
    private function refactorAssign(\PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr\Variable $variable, \PhpParser\Node\Stmt\Foreach_ $foreach, \PhpParser\Node\Expr $condExpr) : ?\PhpParser\Node\Expr\Assign
    {
        if (!$expression->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $assign = $expression->expr;
        // only the array dim fetch with key is allowed
        if (!$assign->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        $arrayDimFetch = $assign->var;
        $arrayDimVariableType = $this->getType($arrayDimFetch->var);
        $arrayType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        if ($arrayType->isSuperTypeOf($arrayDimVariableType)->no()) {
            return null;
        }
        // must be array type
        if (!$arrayDimVariableType instanceof \PHPStan\Type\ArrayType) {
            return null;
        }
        // two different types, probably not empty array
        if ($arrayDimVariableType->getItemType() instanceof \PHPStan\Type\UnionType) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($variable, $assign->expr)) {
            return null;
        }
        // the keyvar must be variable in array dim fetch
        if (!$foreach->keyVar instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($arrayDimFetch->dim, $foreach->keyVar)) {
            return null;
        }
        return $this->arrayFilterFactory->createWithClosure($assign->var, $variable, $condExpr, $foreach);
    }
}
