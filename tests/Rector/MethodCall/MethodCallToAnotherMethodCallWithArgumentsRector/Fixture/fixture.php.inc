<?php

use Rector\Core\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\Source\NetteServiceDefinition;

class SomeNetteExtension
{
    public function loadConfiguration()
    {
        $definition = new NetteServiceDefinition();
        $definition->setInject();
    }
}

?>
-----
<?php

use Rector\Core\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\Source\NetteServiceDefinition;

class SomeNetteExtension
{
    public function loadConfiguration()
    {
        $definition = new NetteServiceDefinition();
        $definition->addTag('inject');
    }
}

?>
