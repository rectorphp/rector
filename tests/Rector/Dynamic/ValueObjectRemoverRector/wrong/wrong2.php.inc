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
    public function someFunction()
    {
        $someChildValueObject = new SomeChildOfValueObject('value');

        $someChildValueObject = new SomeChildOfValueObject();
        $someChildValueObject = new SomeChildOfValueObject('value', 'anotherValue');
    }
}
