<?php declare (strict_types=1);

class SomeNetteExtension extends Nette\DI\CompilerExtension
{
    public function loadConfiguration()
    {
        $builder->parameters['appDir'];
        $this->builder->parameters['appDir'];
        $this->getContainerBuilder()->parameters['appDir'];
    }
}
