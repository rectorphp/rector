<?php

namespace SomeNamespace;

class SomeClass
{
    /**
     * @var \Rector\NodeTypeResolver\Tests\Source\SomeClass
     */
    private $someService;

    public function __construct()
    {
        $this->someService = new \Rector\NodeTypeResolver\Tests\Source\SomeClass();
    }

    public function someMethod()
    {
        $this->someService->createAnotherClass();
    }
}
