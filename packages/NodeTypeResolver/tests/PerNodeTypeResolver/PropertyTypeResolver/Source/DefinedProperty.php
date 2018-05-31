<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver;

final class DefinedProperty
{
    /**
     * @var PropertyType
     */
    private $property;

    private $anotherProperty;

    /**
     * @var string|PropertyTypeResolver\Source\PropertyType
     */
    private $partialNamespaceProperty;

    public function __construct(PropertyType $anotherProperty)
    {
        $this->anotherProperty = $anotherProperty;
    }
}
