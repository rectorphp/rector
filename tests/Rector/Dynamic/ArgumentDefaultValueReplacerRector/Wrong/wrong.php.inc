<?php declare(strict_types=1);

class SomeClass
{
    public function create()
    {
        $containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder;
        $containerBuilder->compile(\Symfony\Component\DependencyInjection\ContainerBuilder::SCOPE_PROTOTYPE);
    }
}
