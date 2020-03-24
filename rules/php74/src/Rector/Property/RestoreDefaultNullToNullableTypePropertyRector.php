<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

/**
 * @see \Rector\Php74\Tests\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\RestoreDefaultNullToNullableTypePropertyRectorTest
 */
final class RestoreDefaultNullToNullableTypePropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add null default to properties with PHP 7.4 property nullable type', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public ?string $name;
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public ?string $name = null;
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

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $onlyProperty = $node->props[0];
        $onlyProperty->default = $this->createNull();

        return $node;
    }

    private function shouldSkip(Property $property): bool
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return true;
        }

        if (! $property->type instanceof NullableType) {
            return true;
        }

        if (count($property->props) > 1) {
            return true;
        }

        $onlyProperty = $property->props[0];

        return $onlyProperty->default !== null;
    }
}
