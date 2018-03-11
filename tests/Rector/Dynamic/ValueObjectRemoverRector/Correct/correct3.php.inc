<?php

namespace SomeNamespace;

class SomeValueObject
{
}

class ActionClass
{
    /**
     * @param null|string $name
     */
    public function someFunction(?string $name): ?string
    {
        /** @var string|null $someValueObject */
        $someValueObject = 'value';
    }
}
