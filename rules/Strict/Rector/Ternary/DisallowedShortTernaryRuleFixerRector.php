<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Analyser\Scope;
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
final class DisallowedShortTernaryRuleFixerRector extends AbstractFalsyScalarRuleFixerRector
{
    /**
     * @readonly
     * @var \Rector\Strict\NodeFactory\ExactCompareFactory
     */
    private $exactCompareFactory;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(ExactCompareFactory $exactCompareFactory)
    {
        $this->exactCompareFactory = $exactCompareFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\\Rules\\DisallowedConstructs\\DisallowedShortTernaryRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Ternary
    {
        $this->hasChanged = \false;
        // skip non-short ternary
        if ($node->if instanceof Expr) {
            return null;
        }
        // special case for reset() function
        if ($node->cond instanceof FuncCall && $this->isName($node->cond, 'reset')) {
            $this->refactorResetFuncCall($node, $node->cond, $scope);
            if (!$this->hasChanged) {
                return null;
            }
            return $node;
        }
        $exprType = $scope->getNativeType($node->cond);
        $compareExpr = $this->exactCompareFactory->createNotIdenticalFalsyCompare($exprType, $node->cond, $this->treatAsNonEmpty);
        if (!$compareExpr instanceof Expr) {
            return null;
        }
        $node->if = $node->cond;
        $node->cond = $compareExpr;
        return $node;
    }
    private function refactorResetFuncCall(Ternary $ternary, FuncCall $resetFuncCall, Scope $scope) : void
    {
        $ternary->if = $ternary->cond;
        if ($resetFuncCall->isFirstClassCallable()) {
            return;
        }
        $firstArgValue = $resetFuncCall->getArgs()[0]->value;
        $firstArgType = $scope->getNativeType($firstArgValue);
        $falsyCompareExpr = $this->exactCompareFactory->createNotIdenticalFalsyCompare($firstArgType, $firstArgValue, $this->treatAsNonEmpty);
        if (!$falsyCompareExpr instanceof Expr) {
            return;
        }
        $ternary->cond = $falsyCompareExpr;
        $this->hasChanged = \true;
    }
}
