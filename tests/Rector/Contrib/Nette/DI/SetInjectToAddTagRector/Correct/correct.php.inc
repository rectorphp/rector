<?php declare (strict_types=1);

class SomeNetteExtension
{
    public function loadConfiguration()
    {
        $definition = new Nette\DI\ServiceDefinition();
        $definition->addTag('inject');
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}