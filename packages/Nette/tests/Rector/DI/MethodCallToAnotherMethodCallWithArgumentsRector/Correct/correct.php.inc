<?php declare (strict_types=1);

use Rector\Nette\Tests\Rector\DI\SetInjectToAddTagRector\Source\NetteServiceDefinition;

class SomeNetteExtension
{
    public function loadConfiguration()
    {
        $definition = new NetteServiceDefinition();
        $definition->addTag('inject');
    }
}
