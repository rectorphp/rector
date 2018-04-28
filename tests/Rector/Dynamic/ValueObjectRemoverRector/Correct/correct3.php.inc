<?php

namespace SomeNamespace;

use Rector\Tests\Rector\Dynamic\ValueObjectRemoverRector\Source\SomeValueObject;

class ActionClass
{
    /**
     * @param null|string $name
     */
    public function someFunction(?string $name): ?string
    {
    }
}
