<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\Empty_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Isset_;
use PHPStan\Analyser\Scope;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PHPStan\ScopeFetcher;
use Rector\Strict\NodeAnalyzer\UninitializedPropertyAnalyzer;
use Rector\Strict\NodeFactory\ExactCompareFactory;
use Rector\Strict\Rector\AbstractFalsyScalarRuleFixerRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\DisallowedEmptyRuleFixerRectorTest
 */
final class DisallowedEmptyRuleFixerRector extends AbstractFalsyScalarRuleFixerRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private ExactCompareFactory $exactCompareFactory;
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    /**
     * @readonly
     */
    private UninitializedPropertyAnalyzer $uninitializedPropertyAnalyzer;
    public function __construct(ExactCompareFactory $exactCompareFactory, ExprAnalyzer $exprAnalyzer, UninitializedPropertyAnalyzer $uninitializedPropertyAnalyzer)
    {
        $this->exactCompareFactory = $exactCompareFactory;
        $this->exprAnalyzer = $exprAnalyzer;
        $this->uninitializedPropertyAnalyzer = $uninitializedPropertyAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        $errorMessage = \sprintf('Fixer for PHPStan reports by strict type rule - "%s"', 'PHPStan\\Rules\\DisallowedConstructs\\DisallowedEmptyRule');
        return new RuleDefinition($errorMessage, [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [Empty_::class, BooleanNot::class];
    }
    /**
     * @param Empty_|BooleanNot $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Expr
    {
        $scope = ScopeFetcher::fetch($node);
        if ($node instanceof BooleanNot) {
            return $this->refactorBooleanNot($node, $scope);
        }
        if ($node->expr instanceof ArrayDimFetch) {
            return null;
        }
        return $this->refactorEmpty($node, $scope, $this->treatAsNonEmpty);
    }
    private function refactorBooleanNot(BooleanNot $booleanNot, Scope $scope) : ?\PhpParser\Node\Expr
    {
        if (!$booleanNot->expr instanceof Empty_) {
            return null;
        }
        $empty = $booleanNot->expr;
        if ($empty->expr instanceof ArrayDimFetch) {
            return $this->createDimFetchBooleanAnd($empty->expr);
        }
        if ($this->exprAnalyzer->isNonTypedFromParam($empty->expr)) {
            return null;
        }
        $emptyExprType = $scope->getNativeType($empty->expr);
        $result = $this->exactCompareFactory->createNotIdenticalFalsyCompare($emptyExprType, $empty->expr, $this->treatAsNonEmpty);
        if (!$result instanceof Expr) {
            return null;
        }
        if ($this->uninitializedPropertyAnalyzer->isUninitialized($empty->expr)) {
            return new BooleanAnd(new Isset_([$empty->expr]), $result);
        }
        return $result;
    }
    private function refactorEmpty(Empty_ $empty, Scope $scope, bool $treatAsNonEmpty) : ?\PhpParser\Node\Expr
    {
        if ($this->exprAnalyzer->isNonTypedFromParam($empty->expr)) {
            return null;
        }
        $exprType = $scope->getNativeType($empty->expr);
        $result = $this->exactCompareFactory->createIdenticalFalsyCompare($exprType, $empty->expr, $treatAsNonEmpty);
        if (!$result instanceof Expr) {
            return null;
        }
        if ($this->uninitializedPropertyAnalyzer->isUninitialized($empty->expr)) {
            return new BooleanOr(new BooleanNot(new Isset_([$empty->expr])), $result);
        }
        return $result;
    }
    private function createDimFetchBooleanAnd(ArrayDimFetch $arrayDimFetch) : ?BooleanAnd
    {
        $exprType = $this->nodeTypeResolver->getNativeType($arrayDimFetch);
        $isset = new Isset_([$arrayDimFetch]);
        $compareExpr = $this->exactCompareFactory->createNotIdenticalFalsyCompare($exprType, $arrayDimFetch, \false);
        if (!$compareExpr instanceof Expr) {
            return null;
        }
        return new BooleanAnd($isset, $compareExpr);
    }
}
