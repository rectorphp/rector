<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Empty_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRectorTest\SimplifyEmptyCheckOnEmptyArrayRectorTest
 */
final class SimplifyEmptyCheckOnEmptyArrayRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify `empty` functions calls on empty arrays', [new CodeSample('$array = []; if(empty($values))', '$array = []; if([] === $values)')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Empty_::class];
    }
    /**
     * @param Empty_ $node $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if (!$this->isAllowedExpr($node->expr)) {
            return null;
        }
        if (!$scope->getType($node->expr) instanceof ArrayType) {
            return null;
        }
        if ($this->exprAnalyzer->isNonTypedFromParam($node->expr)) {
            return null;
        }
        return new Identical($node->expr, new Array_());
    }
    private function isAllowedExpr(Expr $expr) : bool
    {
        if ($expr instanceof Variable) {
            return \true;
        }
        if ($expr instanceof PropertyFetch) {
            return \true;
        }
        return $expr instanceof StaticPropertyFetch;
    }
}
