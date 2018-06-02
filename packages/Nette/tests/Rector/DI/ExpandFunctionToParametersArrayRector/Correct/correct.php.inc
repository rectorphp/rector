<?php declare (strict_types=1);

use Rector\Nette\Tests\Rector\DI\Source\NetteCompilerExtension;

class SomeNetteExtension extends NetteCompilerExtension
{
    /**
     * @var SomeBuilder
     */
    private $builder;

    public function loadConfiguration()
    {
        $builder = new SomeBuilder;
        $builder->parameters['appDir'];
        $this->builder->parameters['appDir'];
        $this->getContainerBuilder()->parameters['appDir'];
    }
}
