<?php

$someService = new SomeClass();

$anotherService = $someService->createAnotherClass();

class SomeClass extends SomeParentClass
{
    public function createAnotherClass(): AnotherClass
    {

    }
}

class SomeParentClass
{

}
