<?php declare (strict_types=1);

class SomeNetteExtension extends Nette\DI\CompilerExtension
{
    public function loadConfiguration()
    {
        $builder->expand('%appDir%');
        $this->builder->expand('%appDir%');
        $this->getContainerBuilder()->expand('%appDir%');
    }
}
