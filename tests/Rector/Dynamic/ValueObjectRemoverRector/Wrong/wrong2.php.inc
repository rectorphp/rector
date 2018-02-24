<?php

namespace SomeNamespace;

class SomeValueObject
{
}

class SomeChildOfValueObject extends SomeValueObject
{
}

class ActionClass
{
    public function someFunction(SomeChildOfValueObject $name)
    {
    }
}
