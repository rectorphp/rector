<?php

namespace SomeNamespace;

class SomeValueObject
{
}

class ActionClass
{
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
    }
}
