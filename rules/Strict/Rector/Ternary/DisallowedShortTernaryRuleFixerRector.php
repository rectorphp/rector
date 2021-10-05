<?php

declare(strict_types=1);

namespace Rector\Strict\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Fixer Rector for PHPStan rule:
 * https://github.com/phpstan/phpstan-strict-rules/blob/master/src/Rules/DisallowedConstructs/DisallowedShortTernaryRule.php
 *
 * @see \Rector\Tests\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector\DisallowedShortTernaryRuleFixerRectorTest
 */
final class DisallowedShortTernaryRuleFixerRector extends AbstractRector
{
    public function __construct(
        private ExactCompareFactory $exactCompareFactory,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $errorMessage = sprintf(
            'Fixer for PHPStan reports by strict type rule - "%s"',
            'PHPStan\Rules\DisallowedConstructs\DisallowedShortTernaryRule'
        );
        return new RuleDefinition($errorMessage, [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class ShortTernaryArray
{
    public function run(array $array)
    {
        return $array ?: 2;
    }
}
CODE_SAMPLE
            ,
                <<<'CODE_SAMPLE'
final class ShortTernaryArray
{
    public function run(array $array)
    {
        return $array !== [] ? $array : 2;
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

        // skip non-short ternary
        if ($node->if !== null) {
            return null;
        }

        // special case for reset() function
        if ($node->cond instanceof FuncCall && $this->isName($node->cond, 'reset')) {
            $this->refactorResetFuncCall($node, $node->cond, $scope);
            return $node;
        }

        $exprType = $scope->getType($node->cond);
        $falsyIdentical = $this->exactCompareFactory->createNotIdenticalFalsyCompare($exprType, $node->cond);
        if ($falsyIdentical === null) {
            return null;
        }

        $node->if = $node->cond;
        $node->cond = $falsyIdentical;

        return $node;
    }

    private function refactorResetFuncCall(Ternary $ternary, FuncCall $resetFuncCall, Scope $scope): void
    {
        $ternary->if = $ternary->cond;

        $firstArgValue = $resetFuncCall->args[0]->value;
        $firstArgType = $scope->getType($firstArgValue);

        $falsyCompareExpr = $this->exactCompareFactory->createNotIdenticalFalsyCompare(
            $firstArgType,
            $firstArgValue
        );

        if ($falsyCompareExpr === null) {
            return;
        }

        $ternary->cond = $falsyCompareExpr;
    }
}
