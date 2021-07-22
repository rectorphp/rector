<?php

declare(strict_types=1);

namespace Nette\DI;

if (class_exists('Nette\DI\CompilerExtension')) {
    return;
}

class CompilerExtension
{
    /**
     * @return ContainerBuilder
     */
    public function getContainerBuilder()
    {
    }
}
