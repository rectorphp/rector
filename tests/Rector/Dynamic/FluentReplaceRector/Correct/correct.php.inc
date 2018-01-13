<?php

namespace SomeNamespace;

class SomeClass
{
    public function someFunction()
    {
        foo();
    }

    public function otherFunction()
    {
        bar();
    }
}

class ActionClass
{
    /**
     * @var SomeClass
     */
    private $someClass;

    public function someFunction()
    {
        $this->someClass = new SomeClass();

        $this->someClass->someFunction()
        $this->someClass->otherFunction();
    }
}
