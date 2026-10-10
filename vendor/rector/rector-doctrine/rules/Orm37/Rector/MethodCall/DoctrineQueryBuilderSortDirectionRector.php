<?php

declare (strict_types=1);
namespace Rector\Doctrine\Orm37\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PHPStan\Type\ObjectType;
use Rector\Doctrine\NodeAnalyzer\SortDirectionAvailabilityResolver;
use Rector\Doctrine\NodeAnalyzer\SortDirectionResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see https://github.com/doctrine/orm/issues/11313
 * @see https://github.com/doctrine/orm/pull/12449
 * @see \Rector\Doctrine\Tests\Orm37\Rector\MethodCall\DoctrineQueryBuilderSortDirectionRector\DoctrineQueryBuilderSortDirectionRectorTest
 */
final class DoctrineQueryBuilderSortDirectionRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private SortDirectionResolver $sortDirectionResolver;
    /**
     * @readonly
     */
    private SortDirectionAvailabilityResolver $sortDirectionAvailabilityResolver;
    public function __construct(SortDirectionResolver $sortDirectionResolver, SortDirectionAvailabilityResolver $sortDirectionAvailabilityResolver)
    {
        $this->sortDirectionResolver = $sortDirectionResolver;
        $this->sortDirectionAvailabilityResolver = $sortDirectionAvailabilityResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces string sort directions with \SortDirection enum in Doctrine QueryBuilder', [new CodeSample(<<<'CODE_SAMPLE'
$queryBuilder->orderBy('e.id', 'ASC');
$queryBuilder->addOrderBy('e.name', 'desc');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$queryBuilder->orderBy('e.id', \SortDirection::Ascending);
$queryBuilder->addOrderBy('e.name', \SortDirection::Descending);
CODE_SAMPLE
)]);
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('doctrine/orm', '>=3.7');
    }
    public function getNodeTypes(): array
    {
        return [MethodCall::class, New_::class];
    }
    /**
     * @param MethodCall|New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->sortDirectionAvailabilityResolver->isAvailable()) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($node instanceof MethodCall) {
            $isQueryBuilder = $this->isTargetType($node->var, 'Doctrine\ORM\QueryBuilder') && $this->isNames($node->name, ['orderBy', 'addOrderBy']);
            $isExpr = $this->isObjectType($node->var, new ObjectType('Doctrine\ORM\Query\Expr')) && $this->isName($node->name, 'orderBy');
            $isOrderByAdd = $this->isObjectType($node->var, new ObjectType('Doctrine\ORM\Query\Expr\OrderBy')) && $this->isName($node->name, 'add');
            if (!$isQueryBuilder && !$isExpr && !$isOrderByAdd) {
                return null;
            }
        }
        if ($node instanceof New_ && !$this->isObjectType($node->class, new ObjectType('Doctrine\ORM\Query\Expr\OrderBy'))) {
            return null;
        }
        $args = $node->getArgs();
        if (count($args) < 2) {
            return null;
        }
        $orderArg = $args[1];
        $orderValue = $this->sortDirectionResolver->resolve($orderArg->value);
        if (!is_string($orderValue)) {
            return null;
        }
        $orderValue = strtolower($orderValue);
        if ($orderValue === 'asc') {
            $orderArg->value = $this->nodeFactory->createClassConstFetch('SortDirection', 'Ascending');
            return $node;
        }
        if ($orderValue === 'desc') {
            $orderArg->value = $this->nodeFactory->createClassConstFetch('SortDirection', 'Descending');
            return $node;
        }
        return null;
    }
    /**
     * Safely checks types even when fluent method chains lose their type mid-mutation.
     */
    private function isTargetType(Node $node, string $className): bool
    {
        if ($this->isObjectType($node, new ObjectType($className))) {
            return \true;
        }
        if ($node instanceof MethodCall) {
            return $this->isTargetType($node->var, $className);
        }
        return \false;
    }
}
