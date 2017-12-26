<?php

use Nette\Config\Configurator;

class SomeClass extends Configurator
{
    public function createContainer()
    {
        return parent::createContainer();
    }
}
