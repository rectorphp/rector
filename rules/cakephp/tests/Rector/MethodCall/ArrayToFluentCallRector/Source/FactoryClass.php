<?php
declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Source;

class FactoryClass
{
    public function buildClass($arg1, array $options = []): ConfigurableClass
    {
        $configurableClass = new ConfigurableClass();

        $configurableClass->setName($options['name']);

        return $configurableClass;
    }
}
