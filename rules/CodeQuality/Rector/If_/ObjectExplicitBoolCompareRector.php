<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ObjectType;
use Rector\CodeQuality\NodeAnalyzer\ExplicitBoolConditionResolver;
use Rector\CodeQuality\ValueObject\ExplicitBoolCondition;
use Rector\PhpParser\Node\NullableObjectToInstanceofFactory;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\ObjectExplicitBoolCompareRector\ObjectExplicitBoolCompareRectorTest
 */
final class ObjectExplicitBoolCompareRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ExplicitBoolConditionResolver $explicitBoolConditionResolver;
    /**
     * @readonly
     */
    private NullableObjectToInstanceofFactory $nullableObjectToInstanceofFactory;
    public function __construct(ExplicitBoolConditionResolver $explicitBoolConditionResolver, NullableObjectToInstanceofFactory $nullableObjectToInstanceofFactory)
    {
        $this->explicitBoolConditionResolver = $explicitBoolConditionResolver;
        $this->nullableObjectToInstanceofFactory = $nullableObjectToInstanceofFactory;
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
        $node->cond = $this->nullableObjectToInstanceofFactory->create($expr, $objectType, $explicitBoolCondition->isNegated());
        return $node;
    }
}
