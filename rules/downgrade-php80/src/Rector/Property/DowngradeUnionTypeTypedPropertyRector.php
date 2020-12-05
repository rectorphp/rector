<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\Property;

use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use Rector\DowngradePhp74\Rector\Property\AbstractDowngradeTypedPropertyRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\Property\DowngradeUnionTypeTypedPropertyRector\DowngradeUnionTypeTypedPropertyRectorTest
 */
final class DowngradeUnionTypeTypedPropertyRector extends AbstractDowngradeTypedPropertyRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Removes union type property type definition, adding `@var` annotations instead.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private string|int $property;
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
    * @var string|int
    */
    private $property;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    public function shouldRemoveProperty(Property $property): bool
    {
        if ($property->type === null) {
            return false;
        }

        // Check it is the union type
        return $property->type instanceof UnionType;
    }
}
