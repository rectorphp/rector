<?php declare (strict_types=1);

use Rector\Nette\Tests\Rector\DI\Source\NetteCompilerExtension;

class SomeNetteExtension extends NetteCompilerExtension
{
    public function loadConfiguration()
    {
        $builder->expand($config['logging']);
        $this->getContainerBuilder()->expand(Nette\DI\Config\Helpers::merge(
            array_diff_key(
                $provided,
                array_diff_key(
                    $diff,
                    $defaults
                )
            ),
            $defaults
        ));
    }
}
