<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\NullsafeMethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Naming\Naming\VariableNaming;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\DowngradeNullsafeToTernaryOperatorRectorTest
 */
final class DowngradeNullsafeToTernaryOperatorRector extends \Rector\Core\Rector\AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change nullsafe operator to ternary operator rector', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$dateAsString = $booking->getStartDate()?->asDateTimeString();
$dateAsString = $booking->startDate?->dateTimeString;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$dateAsString = ($bookingGetStartDate = $booking->getStartDate()) ? $bookingGetStartDate->asDateTimeString() : null;
$dateAsString = ($bookingGetStartDate = $booking->startDate) ? $bookingGetStartDate->dateTimeString : null;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\NullsafeMethodCall::class, \PhpParser\Node\Expr\NullsafePropertyFetch::class];
    }
    /**
     * @param NullsafeMethodCall|NullsafePropertyFetch $node
     */
    public function refactorWithScope(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : \PhpParser\Node\Expr\Ternary
    {
        $tempVarName = $this->variableNaming->resolveFromNodeWithScopeCountAndFallbackName($node->var, $scope, '_');
        $variable = new \PhpParser\Node\Expr\Variable($tempVarName);
        $called = $node instanceof \PhpParser\Node\Expr\NullsafeMethodCall ? new \PhpParser\Node\Expr\MethodCall($variable, $node->name, $node->args) : new \PhpParser\Node\Expr\PropertyFetch($variable, $node->name);
        $assign = new \PhpParser\Node\Expr\Assign($variable, $node->var);
        return new \PhpParser\Node\Expr\Ternary($assign, $called, $this->nodeFactory->createNull());
    }
}
