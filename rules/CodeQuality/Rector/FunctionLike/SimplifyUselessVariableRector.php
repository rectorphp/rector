<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\MixedType;
use Rector\CodeQuality\NodeAnalyzer\ReturnAnalyzer;
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
final class SimplifyUselessVariableRector extends AbstractRector
{
    public function __construct(
        private readonly AssignAndBinaryMap $assignAndBinaryMap,
        private readonly VariableAnalyzer $variableAnalyzer,
        private readonly CallAnalyzer $callAnalyzer,
        private readonly ReturnAnalyzer $returnAnalyzer,
    ) {
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
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FunctionLike::class];
    }

    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node): ?Node
    {
        $stmts = $node->getStmts();
        if ($stmts === null) {
            return null;
        }

        foreach ($stmts as $key => $stmt) {
            if (! isset($stmts[$key - 1])) {
                continue;
            }

            if (! $stmt instanceof Return_) {
                continue;
            }

            $previousStmt = $stmts[$key - 1];
            if ($this->shouldSkipStmt($stmt, $previousStmt)) {
                continue;
            }

            if ($this->isReturnWithVarAnnotation($stmt)) {
                continue;
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

    private function processSimplifyUselessVariable(
        FunctionLike $functionLike,
        Return_ $return,
        Assign|AssignOp $assign,
        int $key
    ): ?FunctionLike {
        if (! $assign instanceof Assign) {
            $binaryClass = $this->assignAndBinaryMap->getAlternative($assign);
            if ($binaryClass === null) {
                return null;
            }

            $return->expr = new $binaryClass($assign->var, $assign->expr);
        } else {
            $return->expr = $assign->expr;
        }

        unset($functionLike->stmts[$key - 1]);
        return $functionLike;
    }

    private function shouldSkipStmt(Return_ $return, Stmt $previousStmt): bool
    {
        if ($this->hasSomeComment($previousStmt)) {
            return true;
        }

        if (! $return->expr instanceof Variable) {
            return true;
        }

        if ($this->returnAnalyzer->hasByRefReturn($return)) {
            return true;
        }

        /** @var Variable $variable */
        $variable = $return->expr;

        if (! $previousStmt instanceof Expression) {
            return true;
        }

        // is variable part of single assign
        $previousNode = $previousStmt->expr;

        if (! $previousNode instanceof AssignOp && ! $previousNode instanceof Assign) {
            return true;
        }

        // is the same variable
        if (! $this->nodeComparator->areNodesEqual($previousNode->var, $variable)) {
            return true;
        }

        if ($this->variableAnalyzer->isStaticOrGlobal($variable)) {
            return true;
        }

        if ($this->callAnalyzer->isNewInstance($previousNode->var)) {
            return true;
        }

        return $this->variableAnalyzer->isUsedByReference($variable);
    }

    private function hasSomeComment(Stmt $stmt): bool
    {
        if ($stmt->getComments() !== []) {
            return true;
        }

        return $stmt->getDocComment() !== null;
    }

    private function isReturnWithVarAnnotation(Return_ $return): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($return);
        return ! $phpDocInfo->getVarType() instanceof MixedType;
    }
}
