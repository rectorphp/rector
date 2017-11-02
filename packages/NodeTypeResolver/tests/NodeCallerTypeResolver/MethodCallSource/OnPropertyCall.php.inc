<?php

namespace SomeNamespace;

use Nette\Config\Configurator;

class SomeClass
{
    /**
     * @var Configurator
     */
    private $configurator;


    public function someMethod()
    {
        $this->configurator->createContainer();
    }
}
