<?php

declare(strict_types=1);

namespace Rector\Strict\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Fixer Rector for PHPStan rule:
 * https://github.com/phpstan/phpstan-strict-rules/blob/master/src/Rules/BooleansInConditions/BooleanInTernaryOperatorRule.php
 *
 * @see \Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\BooleanInTernaryOperatorRuleFixerRectorTest
 */
final class BooleanInTernaryOperatorRuleFixerRector extends AbstractRector
{
    public function __construct(
        private ExactCompareFactory $exactCompareFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $errorMessage = \sprintf(
            'Fixer for PHPStan reports by strict type rule - "%s"',
            'PHPStan\Rules\BooleansInConditions\BooleanInTernaryOperatorRule'
        );
        return new RuleDefinition($errorMessage, [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class ArrayCompare
{
    public function run(array $data)
    {
        return $data ? 1 : 2;
    }
}
CODE_SAMPLE
            ,
                <<<'CODE_SAMPLE'
final class ArrayCompare
{
    public function run(array $data)
    {
        return $data !== [] ? 1 : 2;
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
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Ternary
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        // skip short ternary
        if ($node->if === null) {
            return null;
        }

        $exprType = $scope->getType($node->cond);

        $falsyIdentical = $this->exactCompareFactory->createNotIdenticalFalsyCompare($exprType, $node->cond);
        if (! $falsyIdentical instanceof Expr) {
            return null;
        }

        $node->cond = $falsyIdentical;

        return $node;
    }
}
