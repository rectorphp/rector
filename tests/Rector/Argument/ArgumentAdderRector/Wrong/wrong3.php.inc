<?php declare(strict_types=1);

class CustomContainerBuilder extends Symfony\Component\DependencyInjection\ContainerBuilder
{
    public function addCompilerPass($pass, $type)
    {
    }
}
