<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\ReturnBinaryAndToEarlyReturnRectorTest
 */
final class ReturnBinaryAndToEarlyReturnRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes Single return of && && to early returns', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept($something, $somethingelse)
    {
        return $something && $somethingelse;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept($something, $somethingelse)
    {
        if (!$something) {
            return false;
        }
        return (bool) $somethingelse;
    }
}
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
        if (! $node->expr instanceof BooleanAnd) {
            return null;
        }

        $left = $node->expr->left;
        $ifNegations = $this->createMultipleIfsNegation($left, $node, []);
        $nodeComments = $node->getAttribute(AttributeKey::COMMENTS);

        foreach ($ifNegations as $key => $ifNegation) {
            if ($key === 0) {
                $ifNegation->setAttribute(AttributeKey::COMMENTS, $nodeComments);
            }

            $this->addNodeBeforeNode($ifNegation, $node);
        }

        $lastReturnExpr = $this->getLastReturnExpr($node->expr->right);
        $this->addNodeBeforeNode(new Return_($lastReturnExpr), $node);
        $this->removeNode($node);

        return $node;
    }

    /**
     * @param If_[] $ifNegations
     * @return If_[]
     */
    private function createMultipleIfsNegation(Expr $expr, Return_ $return, array $ifNegations): array
    {
        while ($expr instanceof BooleanAnd) {
            $ifNegations = array_merge($ifNegations, $this->collectLeftBooleanAndToIfs($expr, $return, $ifNegations));
            $ifNegations[] = $this->createIfNegation($expr->right);

            $expr = $expr->right;
        }
        return $ifNegations + [$this->createIfNegation($expr)];
    }

    private function getLastReturnExpr(Expr $expr): Expr
    {
        if ($expr instanceof Bool_) {
            return $expr;
        }

        if ($expr instanceof BooleanNot) {
            return $expr;
        }

        return new Bool_($expr);
    }

    /**
     * @param If_[] $ifNegations
     * @return If_[]
     */
    private function collectLeftBooleanAndToIfs(BooleanAnd $booleanAnd, Return_ $return, array $ifNegations): array
    {
        $left = $booleanAnd->left;
        if (! $left instanceof BooleanAnd) {
            return [$this->createIfNegation($left)];
        }

        return $this->createMultipleIfsNegation($left, $return, $ifNegations);
    }

    private function createIfNegation(Expr $expr): If_
    {
        $expr = $expr instanceof BooleanNot
            ? $expr->expr
            : new BooleanNot($expr);

        return new If_(
            $expr,
            [
                'stmts' => [new Return_($this->createFalse())],
            ]
        );
    }
}
