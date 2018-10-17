<?php

namespace SomeNamespace;

use Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector\Source\SomeValueObject;

class FourthActionClass
{
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
        /** @var string|null $someValueObject */
        $someValueObject = new SomeValueObject('value');
    }
}
