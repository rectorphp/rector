<?php

namespace SomeNamespace;

use Rector\Tests\Rector\Dynamic\ValueObjectRemoverRector\Source\SomeValueObject;

class ActionClass
{
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
        /** @var SomeValueObject|null $someValueObject */
        $someValueObject = new SomeValueObject('value');
    }
}
