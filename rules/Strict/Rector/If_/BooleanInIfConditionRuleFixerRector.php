<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Fixer Rector for PHPStan rules:
 * https://github.com/phpstan/phpstan-strict-rules/blob/master/src/Rules/BooleansInConditions/BooleanInIfConditionRule.php
 * https://github.com/phpstan/phpstan-strict-rules/blob/master/src/Rules/BooleansInConditions/BooleanInElseIfConditionRule.php
 *
 * @see \Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\BooleanInIfConditionRuleFixerRectorTest
 */
final class BooleanInIfConditionRuleFixerRector extends AbstractFalsyScalarRuleFixerRector
{
    /**
     * @readonly
     * @var \Rector\Strict\NodeFactory\ExactCompareFactory
     */
    private $exactCompareFactory;
    public function __construct(ExactCompareFactory $exactCompareFactory)
    {
        $this->exactCompareFactory = $exactCompareFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\\Rules\\BooleansInConditions\\BooleanInIfConditionRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class NegatedString
{
    public function run(string $name)
    {
        if ($name) {
            return 'name';
        }

        return 'no name';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class NegatedString
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
, [self::TREAT_AS_NON_EMPTY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?If_
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        // 1. if
        $ifCondExprType = $scope->getType($node->cond);
        $notIdentical = $this->exactCompareFactory->createNotIdenticalFalsyCompare($ifCondExprType, $node->cond, $this->treatAsNonEmpty);
        if ($notIdentical !== null) {
            $node->cond = $notIdentical;
        }
        // 2. elseifs
        foreach ($node->elseifs as $elseif) {
            $elseifCondExprType = $scope->getType($elseif->cond);
            $notIdentical = $this->exactCompareFactory->createNotIdenticalFalsyCompare($elseifCondExprType, $elseif->cond, $this->treatAsNonEmpty);
            if (!$notIdentical instanceof Expr) {
                continue;
            }
            $elseif->cond = $notIdentical;
        }
        return $node;
    }
}
