<?php declare (strict_types=1);

use Rector\Nette\Tests\Rector\DI\Source\NetteCompilerExtension;

class SomeNetteExtension extends NetteCompilerExtension
{
    public function loadConfiguration()
    {
        $statement = new Nette\DI\Statement('oldEntity');
        $statement->setEntity('newEntity');
    }
}
