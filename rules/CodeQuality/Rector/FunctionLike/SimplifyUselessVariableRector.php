<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\MixedType;
use Rector\CodeQuality\NodeAnalyzer\ReturnAnalyzer;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeAnalyzer\CallAnalyzer;
use Rector\Core\NodeAnalyzer\VariableAnalyzer;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see Based on https://github.com/slevomat/coding-standard/blob/master/SlevomatCodingStandard/Sniffs/Variables/UselessVariableSniff.php
 * @see \Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\SimplifyUselessVariableRectorTest
 */
final class SimplifyUselessVariableRector extends \Rector\Core\Rector\AbstractRector
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
     * @var \Rector\Core\NodeAnalyzer\CallAnalyzer
     */
    private $callAnalyzer;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Node\AssignAndBinaryMap $assignAndBinaryMap, \Rector\Core\NodeAnalyzer\VariableAnalyzer $variableAnalyzer, \Rector\Core\NodeAnalyzer\CallAnalyzer $callAnalyzer, \Rector\CodeQuality\NodeAnalyzer\ReturnAnalyzer $returnAnalyzer)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->variableAnalyzer = $variableAnalyzer;
        $this->callAnalyzer = $callAnalyzer;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes useless variable assigns', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
function () {
    $a = true;
    return $a;
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function () {
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
        return [\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        foreach ($stmts as $key => $stmt) {
            if (!isset($stmts[$key - 1])) {
                continue;
            }
            if (!$stmt instanceof \PhpParser\Node\Stmt\Return_) {
                continue;
            }
            $previousStmt = $stmts[$key - 1];
            if ($this->shouldSkipStmt($stmt, $previousStmt)) {
                return null;
            }
            if ($this->isReturnWithVarAnnotation($stmt)) {
                return null;
            }
            /**
             * @var Expression $previousStmt
             * @var Assign|AssignOp $assign
             */
            $assign = $previousStmt->expr;
            return $this->processSimplifyUselessVariable($node, $stmt, $assign, $key);
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\Assign|\PhpParser\Node\Expr\AssignOp $assign
     */
    private function processSimplifyUselessVariable(\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface $stmtsAware, \PhpParser\Node\Stmt\Return_ $return, $assign, int $key) : ?\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface
    {
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            $binaryClass = $this->assignAndBinaryMap->getAlternative($assign);
            if ($binaryClass === null) {
                return null;
            }
            $return->expr = new $binaryClass($assign->var, $assign->expr);
        } else {
            $return->expr = $assign->expr;
        }
        unset($stmtsAware->stmts[$key - 1]);
        return $stmtsAware;
    }
    private function shouldSkipStmt(\PhpParser\Node\Stmt\Return_ $return, \PhpParser\Node\Stmt $previousStmt) : bool
    {
        if ($this->hasSomeComment($previousStmt)) {
            return \true;
        }
        if (!$return->expr instanceof \PhpParser\Node\Expr\Variable) {
            return \true;
        }
        if ($this->returnAnalyzer->hasByRefReturn($return)) {
            return \true;
        }
        /** @var Variable $variable */
        $variable = $return->expr;
        if (!$previousStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return \true;
        }
        // is variable part of single assign
        $previousNode = $previousStmt->expr;
        if (!$previousNode instanceof \PhpParser\Node\Expr\AssignOp && !$previousNode instanceof \PhpParser\Node\Expr\Assign) {
            return \true;
        }
        // is the same variable
        if (!$this->nodeComparator->areNodesEqual($previousNode->var, $variable)) {
            return \true;
        }
        if ($this->variableAnalyzer->isStaticOrGlobal($variable)) {
            return \true;
        }
        if ($this->callAnalyzer->isNewInstance($previousNode->var)) {
            return \true;
        }
        return $this->variableAnalyzer->isUsedByReference($variable);
    }
    private function hasSomeComment(\PhpParser\Node\Stmt $stmt) : bool
    {
        if ($stmt->getComments() !== []) {
            return \true;
        }
        return $stmt->getDocComment() !== null;
    }
    private function isReturnWithVarAnnotation(\PhpParser\Node\Stmt\Return_ $return) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($return);
        return !$phpDocInfo->getVarType() instanceof \PHPStan\Type\MixedType;
    }
}
