<?php

class SomeClass extends AnotherClass
{
    public function createContainer()
    {
        return $this->createContainer();
    }

    public function createAnotherContainer(): self
    {
        return $this->createAnotherContainer();
    }
}

class AnotherClass extends AnotherParentClass
{

}

class AnotherParentClass
{


}
