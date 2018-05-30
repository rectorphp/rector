<?php

$someService = new SomeClass2();

$anotherService = $someService->createAnotherClass();

class SomeClass2 extends SomeParentClass2
{
    public function createAnotherClass(): AnotherClass
    {

    }
}

class SomeParentClass2
{

}
