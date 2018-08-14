<?php

namespace SomeNamespace;

use Rector\Tests\Rector\DomainDrivenDesign\ValueObjectRemoverDocBlockRector\Source\SomeValueObject;

class ThirdActionClass
{
    /**
     * @param null|string $name
     */
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
    }
}
