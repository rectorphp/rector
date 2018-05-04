<?php

namespace SomeNamespace;

use Rector\Tests\Rector\Dynamic\ValueObjectRemoverRector\Source\SomeValueObject;

class ActionClass
{
    public function someFunction(?string $name): ?string
    {
        /** @var string|null $someValueObject */
        $someValueObject = 'value';
    }
}
