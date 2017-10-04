<?php declare (strict_types=1);

class SomeNetteExtension extends Nette\DI\CompilerExtension
{
    public function loadConfiguration()
    {
        $statement = new Nette\DI\Statement('oldEntity');
        $statement->setEntity('newEntity');
    }
}
