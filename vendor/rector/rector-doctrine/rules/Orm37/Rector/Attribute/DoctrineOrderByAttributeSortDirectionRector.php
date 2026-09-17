<?php

declare (strict_types=1);
namespace Rector\Doctrine\Orm37\Rector\Attribute;

use PhpParser\Node\Expr;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/orm/pull/12454
 * @see \Rector\Doctrine\Tests\Orm37\Rector\Attribute\DoctrineOrderByAttributeSortDirectionRector\DoctrineOrderByAttributeSortDirectionRectorTest
 */
final class DoctrineOrderByAttributeSortDirectionRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces string sort directions with \SortDirection enum in Doctrine #[ORM\OrderBy] Attributes', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SomeClass
{
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    protected $messages;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SomeClass
{
    #[ORM\OrderBy(['createdAt' => \SortDirection::Ascending])]
    protected $messages;
}
CODE_SAMPLE
)]);
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('doctrine/orm', '>=3.7');
    }
    public function getNodeTypes(): array
    {
        return [Attribute::class];
    }
    /**
     * @param Attribute $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'Doctrine\ORM\Mapping\OrderBy')) {
            return null;
        }
        $args = $node->args;
        if ($args === []) {
            return null;
        }
        $firstArgValue = $args[0]->value;
        if (!$firstArgValue instanceof Array_) {
            return null;
        }
        $hasChanged = \false;
        foreach ($firstArgValue->items as $arrayItem) {
            $direction = $this->resolveSortDirectionValue($arrayItem->value);
            if ($direction === 'asc') {
                $arrayItem->value = $this->nodeFactory->createClassConstFetch('SortDirection', 'Ascending');
                $hasChanged = \true;
            } elseif ($direction === 'desc') {
                $arrayItem->value = $this->nodeFactory->createClassConstFetch('SortDirection', 'Descending');
                $hasChanged = \true;
            }
        }
        return $hasChanged ? $node : null;
    }
    private function resolveSortDirectionValue(Expr $expr): ?string
    {
        if ($expr instanceof ClassConstFetch) {
            $constName = $this->getName($expr->name);
            if (is_string($constName)) {
                $normalized = strtolower($constName);
                if (in_array($normalized, ['asc', 'ascending'], \true)) {
                    return 'asc';
                }
                if (in_array($normalized, ['desc', 'descending'], \true)) {
                    return 'desc';
                }
            }
        }
        $value = $this->valueResolver->getValue($expr);
        if (is_string($value)) {
            $normalized = strtolower($value);
            if ($normalized === 'asc' || $normalized === 'desc') {
                return $normalized;
            }
        }
        return null;
    }
}
