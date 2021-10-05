<?php

declare(strict_types=1);

namespace Rector\Strict\Rector\Empty_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Instanceof_;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\DisallowedEmptyRuleFixerRectorTest
 */
final class DisallowedEmptyRuleFixerRector extends AbstractRector
{
    public function __construct(
        private ExactCompareFactory $exactCompareFactory,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $errorMessage = \sprintf(
            'Fixer for PHPStan reports by strict type rule - "%s"',
            'PHPStan\Rules\DisallowedConstructs\DisallowedEmptyRule'
        );
        return new RuleDefinition($errorMessage, [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeEmptyArray
{
    public function run(array $items)
    {
        return empty($items);
    }
}
CODE_SAMPLE
            ,
                <<<'CODE_SAMPLE'
final class SomeEmptyArray
{
    public function run(array $items)
    {
        return $items === [];
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Empty_::class, BooleanNot::class];
    }

    /**
     * @param Empty_|BooleanNot $node
     */
    public function refactor(Node $node)
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        if ($node instanceof BooleanNot) {
            return $this->refactorBooleanNot($node, $scope);
        }

        return $this->refactorEmpty($node, $scope);
    }

    private function refactorBooleanNot(BooleanNot $booleanNot, Scope $scope): NotIdentical|Identical|Instanceof_|null
    {
        if (! $booleanNot->expr instanceof Empty_) {
            return null;
        }

        $empty = $booleanNot->expr;
        $emptyExprType = $scope->getType($empty->expr);

        return $this->exactCompareFactory->createNotIdenticalFalsyCompare($emptyExprType, $empty->expr);
    }

    private function refactorEmpty(Empty_ $empty, Scope $scope): ?Identical
    {
        $exprType = $scope->getType($empty->expr);
        return $this->exactCompareFactory->createIdenticalFalsyCompare($exprType, $empty->expr);
    }
}
