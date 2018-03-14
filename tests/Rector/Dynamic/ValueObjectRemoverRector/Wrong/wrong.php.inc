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
    /**
     * @var SomeChildOfValueObject|null
     */
    private $someChildValueObject;

    public function someFunction()
    {
        $this->someChildValueObject = new SomeChildOfValueObject('value');

        $someChildValueObject = new SomeChildOfValueObject();
    }
}
