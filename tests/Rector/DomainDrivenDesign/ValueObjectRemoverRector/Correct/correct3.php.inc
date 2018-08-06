<?php

namespace SomeNamespace;

use Rector\Tests\Rector\DomainDrivenDesign\ValueObjectRemoverRector\Source\SomeValueObject;

class ThirdActionClass
{
    /**
     * @param null|string $name
     */
    public function someFunction(?string $name): ?string
    {
    }
}
