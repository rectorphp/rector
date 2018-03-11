<?php

namespace SomeNamespace;

class SomeValueObject
{
}

class ActionClass
{
    /**
     * @param null|SomeValueObject $name
     */
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
    }
}
