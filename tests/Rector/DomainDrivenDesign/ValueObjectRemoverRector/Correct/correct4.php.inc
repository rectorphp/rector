<?php

namespace SomeNamespace;

use Rector\Tests\Rector\DomainDrivenDesign\ValueObjectRemoverRector\Source\SomeValueObject;

class FourthActionClass
{
    public function someFunction(?string $name): ?string
    {
        /** @var SomeValueObject|null $someValueObject */
        $someValueObject = 'value';
    }
}
