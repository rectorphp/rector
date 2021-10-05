<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
final class BooleanInTernaryOperatorRuleFixerRector extends \Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector
{
    /**
     * @var \Rector\Strict\NodeFactory\ExactCompareFactory
     */
    private $exactCompareFactory;
    public function __construct(\Rector\Strict\NodeFactory\ExactCompareFactory $exactCompareFactory)
    {
        $this->exactCompareFactory = $exactCompareFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\\Rules\\BooleansInConditions\\BooleanInTernaryOperatorRule');
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition($errorMessage, [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\Ternary
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        // skip short ternary
        if ($node->if === null) {
            return null;
        }
        $exprType = $scope->getType($node->cond);
        $falsyIdentical = $this->exactCompareFactory->createNotIdenticalFalsyCompare($exprType, $node->cond, $this->treatAsNonEmpty);
        if (!$falsyIdentical instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $node->cond = $falsyIdentical;
        return $node;
    }
}
