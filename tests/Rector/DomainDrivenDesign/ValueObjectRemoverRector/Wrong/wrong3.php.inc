<?php

namespace SomeNamespace;

use Rector\Tests\Rector\DomainDrivenDesign\ValueObjectRemoverRector\Source\SomeValueObject;

class ThirdActionClass
{
    /**
     * @param null|SomeValueObject $name
     */
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
    }
}
