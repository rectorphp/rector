<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\MixedType;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see Based on https://github.com/slevomat/coding-standard/blob/master/SlevomatCodingStandard/Sniffs/Variables/UselessVariableSniff.php
 * @see \Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\SimplifyUselessVariableRectorTest
 */
final class SimplifyUselessVariableRector extends AbstractRector
{
    /**
     * @var AssignAndBinaryMap
     */
    private $assignAndBinaryMap;

    public function __construct(AssignAndBinaryMap $assignAndBinaryMap)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Removes useless variable assigns', [
            new CodeSample(
                <<<'CODE_SAMPLE'
function () {
    $a = true;
    return $a;
};
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
function () {
    return true;
};
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $previousNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $previousNode instanceof Expression) {
            return null;
        }

        /** @var AssignOp|Assign $previousNode */
        $previousNode = $previousNode->expr;
        $previousVariableNode = $previousNode->var;

        if ($this->hasSomeComment($previousVariableNode)) {
            return null;
        }

        if ($previousNode instanceof Assign) {
            if ($this->isReturnWithVarAnnotation($node)) {
                return null;
            }

            $node->expr = $previousNode->expr;
        }

        if ($previousNode instanceof AssignOp) {
            $binaryClass = $this->assignAndBinaryMap->getAlternative($previousNode);
            if ($binaryClass === null) {
                return null;
            }

            $node->expr = new $binaryClass($previousNode->var, $previousNode->expr);
        }

        $this->removeNode($previousNode);

        return $node;
    }

    private function shouldSkip(Return_ $return): bool
    {
        if (! $return->expr instanceof Variable) {
            return true;
        }

        $variableNode = $return->expr;

        $previousExpression = $return->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $previousExpression instanceof Node) {
            return true;
        }
        if (! $previousExpression instanceof Expression) {
            return true;
        }

        // is variable part of single assign
        $previousNode = $previousExpression->expr;
        if (! $previousNode instanceof AssignOp && ! $previousNode instanceof Assign) {
            return true;
        }

        // is the same variable
        if (! $this->areNodesEqual($previousNode->var, $variableNode)) {
            return true;
        }
        return $this->isPreviousExpressionVisuallySimilar($previousExpression, $previousNode);
    }

    private function hasSomeComment(Expr $expr): bool
    {
        if ($expr->getComments() !== []) {
            return true;
        }

        return $expr->getDocComment() !== null;
    }

    private function isReturnWithVarAnnotation(Return_ $return): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($return);
        return ! $phpDocInfo->getVarType() instanceof MixedType;
    }

    /**
     * @param AssignOp|Assign $previousNode
     */
    private function isPreviousExpressionVisuallySimilar(Expression $previousExpression, Node $previousNode): bool
    {
        $prePreviousExpression = $previousExpression->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        if (! $prePreviousExpression instanceof Expression) {
            return false;
        }
        if (! $prePreviousExpression->expr instanceof AssignOp) {
            return false;
        }
        return $this->areNodesEqual($prePreviousExpression->expr->var, $previousNode->var);
    }
}
