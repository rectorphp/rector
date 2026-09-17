<?php

declare (strict_types=1);
namespace Rector\Doctrine\Orm32\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PHPStan\Type\ObjectType;
use Rector\Doctrine\NodeAnalyzer\SortDirectionResolver;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/orm/issues/11313
 * @see \Rector\Doctrine\Tests\Orm32\Rector\MethodCall\DoctrineQueryBuilderSortDirectionRector\DoctrineQueryBuilderSortDirectionRectorTest
 */
final class DoctrineQueryBuilderSortDirectionRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private SortDirectionResolver $sortDirectionResolver;
    public function __construct(SortDirectionResolver $sortDirectionResolver)
    {
        $this->sortDirectionResolver = $sortDirectionResolver;
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
        return new ComposerPackageConstraint('doctrine/orm', '>=3.2');
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($node instanceof MethodCall) {
            $isQueryBuilder = $this->isTargetType($node->var, 'Doctrine\ORM\QueryBuilder') && $this->isNames($node->name, ['orderBy', 'addOrderBy']);
            $isExpr = $this->isObjectType($node->var, new ObjectType('Doctrine\ORM\Query\Expr')) && $this->isName($node->name, 'orderBy');
            if (!$isQueryBuilder && !$isExpr) {
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
