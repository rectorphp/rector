<?php

namespace SomeNamespace;

use Rector\Tests\Rector\DomainDrivenDesign\ValueObjectRemoverRector\Source\SomeValueObject;

class ActionClass
{
    /**
     * @param null|string $name
     */
    public function someFunction(?string $name): ?string
    {
    }
}
