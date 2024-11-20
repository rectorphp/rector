<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Ternary;
use Rector\PHPStan\ScopeFetcher;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Fixer Rector for PHPStan rule:
 * https://github.com/phpstan/phpstan-strict-rules/blob/master/src/Rules/BooleansInConditions/BooleanInTernaryOperatorRule.php
 *
 * @see \Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\BooleanInTernaryOperatorRuleFixerRectorTest
 */
final class BooleanInTernaryOperatorRuleFixerRector extends AbstractFalsyScalarRuleFixerRector
{
    /**
     * @readonly
     */
    private ExactCompareFactory $exactCompareFactory;
    public function __construct(ExactCompareFactory $exactCompareFactory)
    {
        $this->exactCompareFactory = $exactCompareFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\\Rules\\BooleansInConditions\\BooleanInTernaryOperatorRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class ArrayCompare
{
    public function run(array $data)
    {
        return $data ? 1 : 2;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class ArrayCompare
{
    public function run(array $data)
    {
        return $data !== [] ? 1 : 2;
    }
}
CODE_SAMPLE
, [self::TREAT_AS_NON_EMPTY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Ternary
    {
        // skip short ternary
        if (!$node->if instanceof Expr) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $exprType = $scope->getNativeType($node->cond);
        $expr = $this->exactCompareFactory->createNotIdenticalFalsyCompare($exprType, $node->cond, $this->treatAsNonEmpty);
        if (!$expr instanceof Expr) {
            return null;
        }
        $node->cond = $expr;
        return $node;
    }
}
