<?php declare (strict_types=1);

use Nette\Configurator;

class ClassWithExternalConstant
{
    public function getValue()
    {
        return Configurator::DEVELOPMENT;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}