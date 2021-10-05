<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Fixer Rector for PHPStan rules:
 * https://github.com/phpstan/phpstan-strict-rules/blob/master/src/Rules/BooleansInConditions/BooleanInIfConditionRule.php
 * https://github.com/phpstan/phpstan-strict-rules/blob/master/src/Rules/BooleansInConditions/BooleanInElseIfConditionRule.php
 *
 * @see \Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\BooleanInIfConditionRuleFixerRectorTest
 */
final class BooleanInIfConditionRuleFixerRector extends \Rector\Core\Rector\AbstractRector
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
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\\Rules\\BooleansInConditions\\BooleanInIfConditionRule');
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition($errorMessage, [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Stmt\If_
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        // 1. if
        $ifCondExprType = $scope->getType($node->cond);
        $notIdentical = $this->exactCompareFactory->createNotIdenticalFalsyCompare($ifCondExprType, $node->cond);
        if ($notIdentical !== null) {
            $node->cond = $notIdentical;
        }
        // 2. elseifs
        foreach ($node->elseifs as $elseif) {
            $elseifCondExprType = $scope->getType($elseif->cond);
            $notIdentical = $this->exactCompareFactory->createNotIdenticalFalsyCompare($elseifCondExprType, $elseif->cond);
            if ($notIdentical === null) {
                continue;
            }
            $elseif->cond = $notIdentical;
        }
        return $node;
    }
}
