<?php

$someService = new SomeClass4;

$files = $someService->someFiles;

class SomeParentClass4
{

}

class SomeClass4 extends SomeParentClass4
{
    public $someFiles;
}
