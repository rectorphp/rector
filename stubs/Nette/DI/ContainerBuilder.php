<?php

declare(strict_types=1);

namespace Nette\DI;

use Nette\DI\Definitions\Definition;

if (class_exists('Nette\DI\ContainerBuilder')) {
    return;
}

final class ContainerBuilder
{
    /**
     * @return \Nette\DI\Definitions\ServiceDefinition
     */
    public function addDefinition(?string $name, Definition $definition = null): Definition
    {
    }
}
