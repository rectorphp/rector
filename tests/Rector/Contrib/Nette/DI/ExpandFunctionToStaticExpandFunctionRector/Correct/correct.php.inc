<?php declare (strict_types=1);

class SomeNetteExtension extends Nette\DI\CompilerExtension
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
