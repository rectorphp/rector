<?php declare(strict_types=1);

class SomeClass
{
    public function create()
    {
        $someService = new AnotherClassWithParentInterface;
        $someService->someMethod();
    }
}

class AnotherClassWithParentInterface implements ParentInterface
{
}

interface ParentInterface
{
}
