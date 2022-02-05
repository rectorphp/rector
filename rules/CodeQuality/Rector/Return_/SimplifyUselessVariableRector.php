<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\MixedType;
use Rector\Core\NodeAnalyzer\CallAnalyzer;
use Rector\Core\NodeAnalyzer\VariableAnalyzer;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see Based on https://github.com/slevomat/coding-standard/blob/master/SlevomatCodingStandard/Sniffs/Variables/UselessVariableSniff.php
 * @see \Rector\Tests\CodeQuality\Rector\Return_\SimplifyUselessVariableRector\SimplifyUselessVariableRectorTest
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
    public function __construct(\Rector\Core\PhpParser\Node\AssignAndBinaryMap $assignAndBinaryMap, \Rector\Core\NodeAnalyzer\VariableAnalyzer $variableAnalyzer, \Rector\Core\NodeAnalyzer\CallAnalyzer $callAnalyzer)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->variableAnalyzer = $variableAnalyzer;
        $this->callAnalyzer = $callAnalyzer;
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
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var Expression $previousExpression */
        $previousExpression = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        /** @var Assign|AssignOp $previousNode */
        $previousNode = $previousExpression->expr;
        $previousVariableNode = $previousNode->var;
        if ($this->hasSomeComment($previousVariableNode)) {
            return null;
        }
        if ($previousNode instanceof \PhpParser\Node\Expr\Assign) {
            if ($this->isReturnWithVarAnnotation($node)) {
                return null;
            }
            $node->expr = $previousNode->expr;
        }
        if ($previousNode instanceof \PhpParser\Node\Expr\AssignOp) {
            $binaryClass = $this->assignAndBinaryMap->getAlternative($previousNode);
            if ($binaryClass === null) {
                return null;
            }
            $node->expr = new $binaryClass($previousNode->var, $previousNode->expr);
        }
        $this->removeNode($previousNode);
        return $node;
    }
    private function hasByRefReturn(\PhpParser\Node\Stmt\Return_ $return) : bool
    {
        $node = $return;
        while ($node = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE)) {
            if ($node instanceof \PhpParser\Node\FunctionLike) {
                return $node->returnsByRef();
            }
            if (!$node instanceof \PhpParser\Node) {
                break;
            }
        }
        return \false;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\Return_ $return) : bool
    {
        if (!$return->expr instanceof \PhpParser\Node\Expr\Variable) {
            return \true;
        }
        if ($this->hasByRefReturn($return)) {
            return \true;
        }
        /** @var Variable $variableNode */
        $variableNode = $return->expr;
        $previousExpression = $return->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if (!$previousExpression instanceof \PhpParser\Node\Stmt\Expression) {
            return \true;
        }
        // is variable part of single assign
        $previousNode = $previousExpression->expr;
        if (!$previousNode instanceof \PhpParser\Node\Expr\AssignOp && !$previousNode instanceof \PhpParser\Node\Expr\Assign) {
            return \true;
        }
        // is the same variable
        if (!$this->nodeComparator->areNodesEqual($previousNode->var, $variableNode)) {
            return \true;
        }
        if ($this->isPreviousExpressionVisuallySimilar($previousExpression, $previousNode)) {
            return \true;
        }
        if ($this->variableAnalyzer->isStaticOrGlobal($variableNode)) {
            return \true;
        }
        return $this->callAnalyzer->isNewInstance($previousNode->var);
    }
    private function hasSomeComment(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr->getComments() !== []) {
            return \true;
        }
        return $expr->getDocComment() !== null;
    }
    private function isReturnWithVarAnnotation(\PhpParser\Node\Stmt\Return_ $return) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($return);
        return !$phpDocInfo->getVarType() instanceof \PHPStan\Type\MixedType;
    }
    /**
     * @param \PhpParser\Node\Expr\Assign|\PhpParser\Node\Expr\AssignOp $previousNode
     */
    private function isPreviousExpressionVisuallySimilar(\PhpParser\Node\Stmt\Expression $previousExpression, $previousNode) : bool
    {
        $prePreviousExpression = $previousExpression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
        if (!$prePreviousExpression instanceof \PhpParser\Node\Stmt\Expression) {
            return \false;
        }
        if (!$prePreviousExpression->expr instanceof \PhpParser\Node\Expr\AssignOp) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($prePreviousExpression->expr->var, $previousNode->var);
    }
}
