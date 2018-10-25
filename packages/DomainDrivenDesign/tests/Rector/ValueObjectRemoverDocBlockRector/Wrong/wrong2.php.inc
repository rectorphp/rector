<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector\Wrong;

use Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector\Source\SomeValueObject;

class FourthActionClass
{
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
        /** @var SomeValueObject|null $someValueObject */
        $someValueObject = new SomeValueObject('value');
    }
}
