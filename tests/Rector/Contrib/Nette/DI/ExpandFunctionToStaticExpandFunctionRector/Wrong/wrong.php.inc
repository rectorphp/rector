<?php declare (strict_types=1);

class SomeNetteExtension extends Nette\DI\CompilerExtension
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
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}