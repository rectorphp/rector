<?php declare (strict_types=1);

class SomeNetteExtension extends Nette\DI\CompilerExtension
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
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}