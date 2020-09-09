<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\Property;

use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DowngradePhp74\Rector\Property\AbstractDowngradeTypedPropertyRector;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\Property\DowngradeUnionTypeTypedPropertyRector\DowngradeUnionTypeTypedPropertyRectorTest
 */
final class DowngradeUnionTypeTypedPropertyRector extends AbstractDowngradeTypedPropertyRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes union type property type definition, adding `@var` annotations instead.', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    private string|int $property;
}
PHP
,
                <<<'PHP'
class SomeClass
{
    /**
    * @var string|int
    */
    private $property;
}
PHP
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
