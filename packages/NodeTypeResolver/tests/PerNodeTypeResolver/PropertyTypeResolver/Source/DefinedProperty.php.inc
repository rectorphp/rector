<?php

namespace SomeNamespace;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\PropertyType;

class SomeClassWithProperties
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
