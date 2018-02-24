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
        $builder->expand('%appDir%');
        $this->builder->expand('%appDir%');
        $this->getContainerBuilder()->expand('%appDir%');
    }    protected function provideConfig(): string
}
