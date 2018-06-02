<?php declare (strict_types=1);

use Rector\Nette\Tests\Rector\DI\Source\NetteCompilerExtension;

class SomeNetteExtension extends NetteCompilerExtension
{
    public function loadConfiguration()
    {
        Nette\DI\Helpers::expand($config['logging'], $builder->parameters);
        Nette\DI\Helpers::expand(Nette\DI\Config\Helpers::merge(
            array_diff_key(
                $provided,
                array_diff_key(
                    $diff,
                    $defaults
                )
            ),
            $defaults
        ), $this->getContainerBuilder()->parameters);
    }
}
