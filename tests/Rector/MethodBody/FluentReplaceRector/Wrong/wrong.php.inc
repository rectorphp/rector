<?php

namespace SomeNamespace;

class SomeClass
{
    public function someFunction()
    {
        foo();
        return $this;
    }

    public function otherFunction()
    {
        bar();
        return $this;
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
                        ->otherFunction();
    }
}
