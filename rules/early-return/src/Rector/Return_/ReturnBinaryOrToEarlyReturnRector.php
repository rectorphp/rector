<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\BooleanType;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\ReturnBinaryOrToEarlyReturnRectorTest
 */
final class ReturnBinaryOrToEarlyReturnRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var AssignAndBinaryMap
     */
    private $assignAndBinaryMap;

    public function __construct(IfManipulator $ifManipulator, AssignAndBinaryMap $assignAndBinaryMap)
    {
        $this->ifManipulator = $ifManipulator;
        $this->assignAndBinaryMap = $assignAndBinaryMap;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes Single return of || to early returns', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept($something, $somethingelse)
    {
        return $something || $somethingelse;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept($something, $somethingelse)
    {
        if ($something) {
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
        if (! $node->expr instanceof BooleanOr) {
            return null;
        }

        $left = $node->expr->left;
        $ifs = $this->createMultipleIfs($left, $node, []);

        if ($ifs === []) {
            return null;
        }

        foreach ($ifs as $key => $if) {
            if ($key === 0) {
                $this->mirrorComments($if, $node);
            }

            $this->addNodeBeforeNode($if, $node);
        }

        $lastReturnExpr = $this->assignAndBinaryMap->getTruthyExpr($node->expr->right);
        $this->addNodeBeforeNode(new Return_($lastReturnExpr), $node);
        $this->removeNode($node);

        return $node;
    }

    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function createMultipleIfs(Expr $expr, Return_ $return, array $ifs): array
    {
        while ($expr instanceof BooleanOr) {
            $ifs = array_merge($ifs, $this->collectLeftBooleanOrToIfs($expr, $return, $ifs));
            $ifs[] = $this->ifManipulator->createIfExpr(
                $expr->right,
                new Return_($this->nodeFactory->createFalse())
            );

            $expr = $expr->right;
            if ($expr instanceof BooleanAnd) {
                return [];
            }
        }

        return $ifs + [$this->ifManipulator->createIfExpr($expr, new Return_($this->nodeFactory->createFalse()))];
    }

    private function getLastReturnExpr(Expr $expr): Expr
    {
        if ($expr instanceof Bool_) {
            return $expr;
        }

        if ($expr instanceof BooleanNot) {
            return $expr;
        }

        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return new Bool_($expr);
        }

        $type = $scope->getType($expr);
        if ($type instanceof BooleanType) {
            return $expr;
        }

        return new Bool_($expr);
    }

    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function collectLeftBooleanOrToIfs(BooleanOr $BooleanOr, Return_ $return, array $ifs): array
    {
        $left = $BooleanOr->left;
        if (! $left instanceof BooleanOr) {
            return [$this->ifManipulator->createIfExpr($left, new Return_($this->nodeFactory->createFalse()))];
        }

        return $this->createMultipleIfs($left, $return, $ifs);
    }
}
