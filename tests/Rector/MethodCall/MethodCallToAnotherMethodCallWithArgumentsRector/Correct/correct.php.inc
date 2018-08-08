<?php declare (strict_types=1);

use Rector\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\Source\NetteServiceDefinition;

class SomeNetteExtension
{
    public function loadConfiguration()
    {
        $definition = new NetteServiceDefinition();
        $definition->addTag('inject');
    }
}
