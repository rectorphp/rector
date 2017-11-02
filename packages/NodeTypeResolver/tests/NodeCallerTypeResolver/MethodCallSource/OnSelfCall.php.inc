<?php

use Nette\Config\Configurator;

class SomeClass extends Configurator
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
