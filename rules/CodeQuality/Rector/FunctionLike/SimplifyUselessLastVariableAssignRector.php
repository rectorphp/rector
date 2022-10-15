<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\MixedType;
use Rector\CodeQuality\NodeAnalyzer\ReturnAnalyzer;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\NodeAnalyzer\VariableAnalyzer;
use Rector\Core\NodeManipulator\ArrayManipulator;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessLastVariableAssignRector\SimplifyUselessLastVariableAssignRectorTest
 */
final class SimplifyUselessLastVariableAssignRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\AssignAndBinaryMap
     */
    private $assignAndBinaryMap;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\VariableAnalyzer
     */
    private $variableAnalyzer;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ArrayManipulator
     */
    private $arrayManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(AssignAndBinaryMap $assignAndBinaryMap, VariableAnalyzer $variableAnalyzer, ReturnAnalyzer $returnAnalyzer, ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer, ArrayManipulator $arrayManipulator, ExprAnalyzer $exprAnalyzer)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->variableAnalyzer = $variableAnalyzer;
        $this->returnAnalyzer = $returnAnalyzer;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
        $this->arrayManipulator = $arrayManipulator;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes the latest useless variable assigns before a variable will return.', [new CodeSample(<<<'CODE_SAMPLE'
function ($b) {
    $a = true;
    if ($b === 1) {
        return $b;
    }
    return $a;
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function ($b) {
    if ($b === 1) {
        return $b;
    }
    return true;
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if ($this->shouldSkip($stmt)) {
                return null;
            }
            $assignStmt = $this->getLatestVariableAssignment($stmts, $stmt);
            if (!$assignStmt instanceof Expression) {
                return null;
            }
            if ($this->shouldSkipOnAssignStmt($assignStmt)) {
                return null;
            }
            if ($this->isAssigmentUseless($stmts, $assignStmt, $stmt)) {
                return null;
            }
            $assign = $assignStmt->expr;
            if (!$assign instanceof Assign && !$assign instanceof AssignOp) {
                return null;
            }
            $this->removeNode($assignStmt);
            return $this->processSimplifyUselessVariable($node, $stmt, $assign);
        }
        return null;
    }
    private function shouldSkip(Return_ $return) : bool
    {
        $variable = $return->expr;
        if (!$variable instanceof Variable) {
            return \true;
        }
        if ($this->returnAnalyzer->hasByRefReturn($return)) {
            return \true;
        }
        if ($this->variableAnalyzer->isStaticOrGlobal($variable)) {
            return \true;
        }
        if ($this->variableAnalyzer->isUsedByReference($variable)) {
            return \true;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($return);
        return !$phpDocInfo->getVarType() instanceof MixedType;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function getLatestVariableAssignment(array $stmts, Return_ $return) : ?Expression
    {
        $returnVariable = $return->expr;
        if (!$returnVariable instanceof Variable) {
            return null;
        }
        //Search for the latest variable assigment
        foreach (\array_reverse($stmts) as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            $assignNode = $stmt->expr;
            if (!$assignNode instanceof Assign && !$assignNode instanceof AssignOp) {
                continue;
            }
            $currentVariableNode = $assignNode->var;
            if (!$currentVariableNode instanceof Variable) {
                continue;
            }
            if ($this->nodeNameResolver->areNamesEqual($returnVariable, $currentVariableNode)) {
                return $stmt;
            }
        }
        return null;
    }
    private function shouldSkipOnAssignStmt(Expression $expression) : bool
    {
        if ($this->hasSomeComment($expression)) {
            return \true;
        }
        $assign = $expression->expr;
        if (!$assign instanceof Assign && !$assign instanceof AssignOp) {
            return \true;
        }
        $variable = $assign->var;
        if (!$variable instanceof Variable) {
            return \true;
        }
        $value = $assign->expr;
        if ($this->exprAnalyzer->isDynamicExpr($value)) {
            return \true;
        }
        return $value instanceof Array_ && $this->arrayManipulator->isDynamicArray($value);
    }
    private function hasSomeComment(Expression $expression) : bool
    {
        if ($expression->getComments() !== []) {
            return \true;
        }
        return $expression->getDocComment() !== null;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isAssigmentUseless(array $stmts, Expression $expression, Return_ $return) : bool
    {
        $assign = $expression->expr;
        if (!$assign instanceof Assign && !$assign instanceof AssignOp) {
            return \false;
        }
        $variable = $assign->var;
        if (!$variable instanceof Variable) {
            return \false;
        }
        $nodesInRange = $this->getNodesInRange($stmts, $expression, $return);
        if ($nodesInRange === null) {
            return \false;
        }
        //Find the variable usage
        $variableUsageNodes = $this->betterNodeFinder->find($nodesInRange, function (Node $node) use($variable) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $variable);
        });
        //Should be exactly used 2 times (assignment + return)
        return \count($variableUsageNodes) !== 2;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]|null
     */
    private function getNodesInRange(array $stmts, Expression $expression, Return_ $return) : ?array
    {
        $resultStmts = [];
        $wasStarted = \false;
        foreach ($stmts as $stmt) {
            if ($stmt === $expression) {
                $wasStarted = \true;
            }
            if ($wasStarted) {
                $resultStmts[] = $stmt;
            }
            if ($stmt === $return) {
                return $resultStmts;
            }
        }
        if ($wasStarted) {
            // This should not happen, if you land here check your given parameter
            return null;
        }
        return $resultStmts;
    }
    /**
     * @param \PhpParser\Node\Expr\Assign|\PhpParser\Node\Expr\AssignOp $assign
     */
    private function processSimplifyUselessVariable(StmtsAwareInterface $stmtsAware, Return_ $return, $assign) : ?StmtsAwareInterface
    {
        if (!$assign instanceof Assign) {
            $binaryClass = $this->assignAndBinaryMap->getAlternative($assign);
            if ($binaryClass === null) {
                return null;
            }
            $return->expr = new $binaryClass($assign->var, $assign->expr);
        } else {
            $return->expr = $assign->expr;
        }
        return $stmtsAware;
    }
}
