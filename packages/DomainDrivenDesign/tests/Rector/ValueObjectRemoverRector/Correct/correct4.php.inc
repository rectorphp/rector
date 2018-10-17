<?php

namespace SomeNamespace;

use Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverRector\Source\SomeValueObject;

class FourthActionClass
{
    public function someFunction(?string $name): ?string
    {
        /** @var SomeValueObject|null $someValueObject */
        $someValueObject = 'value';
    }
}
