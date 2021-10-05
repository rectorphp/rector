<?php

declare(strict_types=1);

namespace Rector\Strict\Rector\BooleanNot;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Fixer Rector for PHPStan rule:
 * https://github.com/phpstan/phpstan-strict-rules/blob/0705fefc7c9168529fd130e341428f5f10f4f01d/src/Rules/BooleansInConditions/BooleanInBooleanNotRule.php
 *
 * @see \Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\BooleanInBooleanNotRuleFixerRectorTest
 */
final class BooleanInBooleanNotRuleFixerRector extends AbstractRector
{
    public function __construct(
        private ExactCompareFactory $exactCompareFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $errorMessage = \sprintf(
            'Fixer for PHPStan reports by strict type rule - "%s"',
            'PHPStan\Rules\BooleansInConditions\BooleanInBooleanNotRule'
        );
        return new RuleDefinition($errorMessage, [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string $name)
    {
        if (! $name) {
            return 'name';
        }

        return 'no name';
    }
}
CODE_SAMPLE
            ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string $name)
    {
        if ($name !== '') {
            return 'name';
        }

        return 'no name';
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
        return [BooleanNot::class];
    }

    /**
     * @param BooleanNot $node
     */
    public function refactor(Node $node): ?Identical
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $exprType = $scope->getType($node->expr);

        return $this->exactCompareFactory->createIdenticalFalsyCompare($exprType, $node->expr);
    }
}
