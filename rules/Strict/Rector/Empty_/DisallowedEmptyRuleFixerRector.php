<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\Empty_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\DisallowedEmptyRuleFixerRectorTest
 */
final class DisallowedEmptyRuleFixerRector extends \Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector
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
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\\Rules\\DisallowedConstructs\\DisallowedEmptyRule');
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition($errorMessage, [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeEmptyArray
{
    public function run(array $items)
    {
        return empty($items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeEmptyArray
{
    public function run(array $items)
    {
        return $items === [];
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
        return [\PhpParser\Node\Expr\Empty_::class, \PhpParser\Node\Expr\BooleanNot::class];
    }
    /**
     * @param Empty_|BooleanNot $node
     * @return \PhpParser\Node\Expr|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\BooleanNot) {
            return $this->refactorBooleanNot($node, $scope);
        }
        return $this->refactorEmpty($node, $scope, $this->treatAsNonEmpty);
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    private function refactorBooleanNot(\PhpParser\Node\Expr\BooleanNot $booleanNot, \PHPStan\Analyser\Scope $scope)
    {
        if (!$booleanNot->expr instanceof \PhpParser\Node\Expr\Empty_) {
            return null;
        }
        $empty = $booleanNot->expr;
        $emptyExprType = $scope->getType($empty->expr);
        return $this->exactCompareFactory->createNotIdenticalFalsyCompare($emptyExprType, $empty->expr, $this->treatAsNonEmpty);
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    private function refactorEmpty(\PhpParser\Node\Expr\Empty_ $empty, \PHPStan\Analyser\Scope $scope, bool $treatAsNonEmpty)
    {
        $exprType = $scope->getType($empty->expr);
        return $this->exactCompareFactory->createIdenticalFalsyCompare($exprType, $empty->expr, $treatAsNonEmpty);
    }
}
