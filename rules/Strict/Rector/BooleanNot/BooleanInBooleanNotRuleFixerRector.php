<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\BooleanNot;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Fixer Rector for PHPStan rule:
 * https://github.com/phpstan/phpstan-strict-rules/blob/0705fefc7c9168529fd130e341428f5f10f4f01d/src/Rules/BooleansInConditions/BooleanInBooleanNotRule.php
 *
 * @see \Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\BooleanInBooleanNotRuleFixerRectorTest
 */
final class BooleanInBooleanNotRuleFixerRector extends \Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector
{
    /**
     * @readonly
     * @var \Rector\Strict\NodeFactory\ExactCompareFactory
     */
    private $exactCompareFactory;
    public function __construct(\Rector\Strict\NodeFactory\ExactCompareFactory $exactCompareFactory)
    {
        $this->exactCompareFactory = $exactCompareFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\\Rules\\BooleansInConditions\\BooleanInBooleanNotRule');
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition($errorMessage, [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string|null $name)
    {
        if (! $name) {
            return 'no name';
        }

        return 'name';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string $name)
    {
        if ($name === null) {
            return 'no name';
        }

        return 'name';
    }
}
CODE_SAMPLE
, [self::TREAT_AS_NON_EMPTY => \true])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BooleanNot::class];
    }
    /**
     * @param BooleanNot $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $exprType = $scope->getType($node->expr);
        return $this->exactCompareFactory->createIdenticalFalsyCompare($exprType, $node->expr, $this->treatAsNonEmpty);
    }
}
