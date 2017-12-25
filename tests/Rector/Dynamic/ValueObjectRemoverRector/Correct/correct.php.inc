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
     * @var string
     */
    private $someChildValueObject;

    public function someFunction()
    {
        $this->someChildValueObject = 'value';

        $someChildValueObject = new SomeChildOfValueObject();
    }
}
