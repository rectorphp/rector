<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\UnionType;
use Rector\CodeQuality\NodeFactory\ArrayFilterFactory;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Rector\ReadWrite\NodeAnalyzer\ReadExprAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\SimplifyForeachToArrayFilterRectorTest
 */
final class SimplifyForeachToArrayFilterRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeFactory\ArrayFilterFactory
     */
    private $arrayFilterFactory;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\ReadWrite\NodeAnalyzer\ReadExprAnalyzer
     */
    private $readExprAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(ArrayFilterFactory $arrayFilterFactory, ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer, ReadExprAnalyzer $readExprAnalyzer, PhpVersionProvider $phpVersionProvider)
    {
        $this->arrayFilterFactory = $arrayFilterFactory;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
        $this->readExprAnalyzer = $readExprAnalyzer;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify foreach with function filtering to array filter', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $foreachValueVar = $node->valueVar;
        if (!$foreachValueVar instanceof Variable) {
            return null;
        }
        /** @var If_ $ifNode */
        $ifNode = $node->stmts[0];
        $condExpr = $ifNode->cond;
        $foreachKeyVar = $node->keyVar;
        if ($foreachKeyVar instanceof Expr && $this->shouldSkipForeachKeyUsage($ifNode, $foreachKeyVar)) {
            return null;
        }
        if ($condExpr instanceof FuncCall) {
            return $this->refactorFuncCall($ifNode, $condExpr, $node, $foreachValueVar);
        }
        $onlyStmt = $ifNode->stmts[0];
        if ($onlyStmt instanceof Expression) {
            return $this->refactorAssign($onlyStmt, $foreachValueVar, $node, $condExpr);
        }
        // another condition - not supported yet
        return null;
    }
    private function shouldSkip(Foreach_ $foreach) : bool
    {
        if (\count($foreach->stmts) !== 1) {
            return \true;
        }
        if (!$foreach->stmts[0] instanceof If_) {
            return \true;
        }
        /** @var If_ $ifNode */
        $ifNode = $foreach->stmts[0];
        if ($ifNode->else instanceof Else_) {
            return \true;
        }
        return $ifNode->elseifs !== [];
    }
    private function shouldSkipForeachKeyUsage(If_ $if, Expr $expr) : bool
    {
        if (!$expr instanceof Variable) {
            return \false;
        }
        /** @var Variable[] $keyVarUsage */
        $keyVarUsage = $this->betterNodeFinder->find($if, function (Node $node) use($expr) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $expr);
        });
        $keyVarUsageCount = \count($keyVarUsage);
        if ($keyVarUsageCount === 1) {
            /** @var Variable $currentVarUsage */
            $currentVarUsage = \current($keyVarUsage);
            return !$this->readExprAnalyzer->isExprRead($currentVarUsage);
        }
        return $keyVarUsageCount !== 0;
    }
    private function isArrayDimFetchInForLoop(Foreach_ $foreach, ArrayDimFetch $arrayDimFetch) : bool
    {
        $loopVar = $foreach->expr;
        if (!$loopVar instanceof Variable) {
            return \false;
        }
        $varThatIsModified = $arrayDimFetch->var;
        if (!$varThatIsModified instanceof Variable) {
            return \false;
        }
        return $loopVar->name !== $varThatIsModified->name;
    }
    private function isSimpleFuncCallOnForeachedVariables(FuncCall $funcCall, Variable $foreachVariable) : bool
    {
        if (\count($funcCall->args) !== 1) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($funcCall->args[0], $foreachVariable);
    }
    private function refactorFuncCall(If_ $if, FuncCall $funcCall, Foreach_ $foreach, Variable $foreachVariable) : ?Assign
    {
        if (\count($if->stmts) !== 1) {
            return null;
        }
        if (!$this->isSimpleFuncCallOnForeachedVariables($funcCall, $foreachVariable)) {
            return null;
        }
        if (!$if->stmts[0] instanceof Expression) {
            return null;
        }
        $onlyNodeInIf = $if->stmts[0]->expr;
        if (!$onlyNodeInIf instanceof Assign) {
            return null;
        }
        $arrayDimFetch = $onlyNodeInIf->var;
        if (!$arrayDimFetch instanceof ArrayDimFetch) {
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
    private function refactorAssign(Expression $expression, Variable $variable, Foreach_ $foreach, Expr $condExpr) : ?Assign
    {
        if (!$expression->expr instanceof Assign) {
            return null;
        }
        $assign = $expression->expr;
        // only the array dim fetch with key is allowed
        if (!$assign->var instanceof ArrayDimFetch) {
            return null;
        }
        $arrayDimFetch = $assign->var;
        $arrayDimVariableType = $this->getType($arrayDimFetch->var);
        $arrayType = new ArrayType(new MixedType(), new MixedType());
        if ($arrayType->isSuperTypeOf($arrayDimVariableType)->no()) {
            return null;
        }
        // must be array type
        if (!$arrayDimVariableType instanceof ArrayType) {
            return null;
        }
        // two different types, probably not empty array
        if ($arrayDimVariableType->getItemType() instanceof UnionType) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($variable, $assign->expr)) {
            return null;
        }
        // the keyvar must be variable in array dim fetch
        $keyVar = $foreach->keyVar;
        if (!$keyVar instanceof Variable) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($arrayDimFetch->dim, $foreach->keyVar)) {
            return null;
        }
        return $this->arrayFilterFactory->createWithClosure($assign->var, $variable, $condExpr, $foreach, $this->getUsedVariablesForClosure($keyVar, $variable, $condExpr));
    }
    /**
     * @return Variable[]
     */
    private function getUsedVariablesForClosure(Variable $keyVar, Variable $valueVar, Expr $condExpr) : array
    {
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::ARROW_FUNCTION)) {
            return [];
        }
        /** @var Variable[] $filteredVariables */
        $filteredVariables = $this->betterNodeFinder->find($condExpr, function (Node $node) use($keyVar, $valueVar) : bool {
            return $node instanceof Variable && !$this->nodeComparator->areNodesEqual($keyVar, $node) && !$this->nodeComparator->areNodesEqual($valueVar, $node) && !$this->nodeNameResolver->isName($node, 'this');
        });
        $uniqueVariables = [];
        foreach ($filteredVariables as $filteredVariable) {
            $variableName = $this->nodeNameResolver->getName($filteredVariable);
            $uniqueVariables[$variableName] = $filteredVariable;
        }
        return \array_values($uniqueVariables);
    }
}
