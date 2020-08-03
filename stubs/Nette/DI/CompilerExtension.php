<?php

declare(strict_types=1);

namespace Nette\DI;

if (class_exists('Nette\DI\CompilerExtension')) {
    return;
}

abstract class CompilerExtension
{
    public function getContainerBuilder(): ContainerBuilder
    {
        return new ContainerBuilder();
    }
}
