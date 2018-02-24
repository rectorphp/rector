<?php declare (strict_types=1);

class SomeNetteExtension
{
    public function loadConfiguration()
    {
        $definition = new Nette\DI\ServiceDefinition();
        $definition->setInject();
    }
}