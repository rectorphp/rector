<?php

class SomeParentCallingClass extends \Rector\NodeTypeResolver\Tests\Source\AnotherClass
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
