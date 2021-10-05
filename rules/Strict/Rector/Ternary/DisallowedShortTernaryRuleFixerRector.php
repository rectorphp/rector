<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Fixer Rector for PHPStan rule:
 * https://github.com/phpstan/phpstan-strict-rules/blob/master/src/Rules/DisallowedConstructs/DisallowedShortTernaryRule.php
 *
 * @see \Rector\Tests\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector\DisallowedShortTernaryRuleFixerRectorTest
 */
final class DisallowedShortTernaryRuleFixerRector extends \Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector
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
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\\Rules\\DisallowedConstructs\\DisallowedShortTernaryRule');
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition($errorMessage, [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class ShortTernaryArray
{
    public function run(array $array)
    {
        return $array ?: 2;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class ShortTernaryArray
{
    public function run(array $array)
    {
        return $array !== [] ? $array : 2;
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
        // skip non-short ternary
        if ($node->if !== null) {
            return null;
        }
        // special case for reset() function
        if ($node->cond instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($node->cond, 'reset')) {
            $this->refactorResetFuncCall($node, $node->cond, $scope);
            return $node;
        }
        $exprType = $scope->getType($node->cond);
        $compareExpr = $this->exactCompareFactory->createNotIdenticalFalsyCompare($exprType, $node->cond, $this->treatAsNonEmpty);
        if (!$compareExpr instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $node->if = $node->cond;
        $node->cond = $compareExpr;
        return $node;
    }
    private function refactorResetFuncCall(\PhpParser\Node\Expr\Ternary $ternary, \PhpParser\Node\Expr\FuncCall $resetFuncCall, \PHPStan\Analyser\Scope $scope) : void
    {
        $ternary->if = $ternary->cond;
        $firstArgValue = $resetFuncCall->args[0]->value;
        $firstArgType = $scope->getType($firstArgValue);
        $falsyCompareExpr = $this->exactCompareFactory->createNotIdenticalFalsyCompare($firstArgType, $firstArgValue, $this->treatAsNonEmpty);
        if (!$falsyCompareExpr instanceof \PhpParser\Node\Expr) {
            return;
        }
        $ternary->cond = $falsyCompareExpr;
    }
}
