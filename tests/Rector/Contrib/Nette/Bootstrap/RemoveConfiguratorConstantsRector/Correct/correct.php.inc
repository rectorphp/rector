<?php declare (strict_types=1);

use Nette\Configurator;

class ClassWithExternalConstant
{
    public function getValue()
    {
        return 'development';
    }
}