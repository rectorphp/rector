<?php

namespace SomeNamespace;

use Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverRector\Source\SomeValueObject;

class ThirdActionClass
{
    /**
     * @param null|SomeValueObject $name
     */
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
    }
}
