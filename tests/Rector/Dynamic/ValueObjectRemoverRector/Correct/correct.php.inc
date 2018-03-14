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
     * @var string|null
     */
    private $someChildValueObject;

    public function someFunction()
    {
        $this->someChildValueObject = 'value';

        $someChildValueObject = new SomeChildOfValueObject();
    }
}
