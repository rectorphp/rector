<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ObjectType;
use Rector\CodeQuality\NodeAnalyzer\ExplicitBoolConditionResolver;
use Rector\CodeQuality\ValueObject\ExplicitBoolCondition;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\ObjectExplicitBoolCompareRector\ObjectExplicitBoolCompareRectorTest
 */
final class ObjectExplicitBoolCompareRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ExplicitBoolConditionResolver $explicitBoolConditionResolver;
    public function __construct(ExplicitBoolConditionResolver $explicitBoolConditionResolver)
    {
        $this->explicitBoolConditionResolver = $explicitBoolConditionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make nullable object if conditions more explicit', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    public function run(?\stdClass $item)
    {
        if (!$item) {
            return 'empty';
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    public function run(?\stdClass $item)
    {
        if (!$item instanceof \stdClass) {
            return 'empty';
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class, ElseIf_::class, Ternary::class];
    }
    /**
     * @param If_|ElseIf_|Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        $explicitBoolCondition = $this->explicitBoolConditionResolver->resolve($node);
        if (!$explicitBoolCondition instanceof ExplicitBoolCondition) {
            return null;
        }
        $expr = $explicitBoolCondition->getConditionNode();
        $objectType = $this->nodeTypeResolver->matchNullableTypeOfSpecificType($expr, ObjectType::class);
        if (!$objectType instanceof ObjectType) {
            return null;
        }
        $node->cond = $this->resolveNullable($explicitBoolCondition->isNegated(), $expr, $objectType);
        return $node;
    }
    /**
     * @return \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_
     */
    private function resolveNullable(bool $isNegated, Expr $expr, ObjectType $objectType)
    {
        $fullyQualified = new FullyQualified($objectType->getClassName());
        $instanceof = new Instanceof_($expr, $fullyQualified);
        return $isNegated ? new BooleanNot($instanceof) : $instanceof;
    }
}
