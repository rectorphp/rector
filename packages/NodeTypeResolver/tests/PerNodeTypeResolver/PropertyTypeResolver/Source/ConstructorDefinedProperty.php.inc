<?php

namespace SomeNamespace;

class SomeClass
{
    private $property;

    public function __construct(PropertyType $property)
    {
        $this->property = $property;
    }
}
