<?php

namespace SomeNamespace;

class SomeValueObject
{
}

class ActionClass
{
    /**
     * @param SomeValueObject|null $name
     */
    public function someFunction(?SomeValueObject $name)
    {
    }
}
