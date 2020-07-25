<?php

declare(strict_types=1);

namespace Nette\ComponentModel;

if (class_exists('Nette\ComponentModel\Container')) {
    return;
}

abstract class Container implements IContainer
{
    /**
     * @var IComponent[]
     */
    private $components = [];

    public function getComponent(string $name): ?IComponent
    {
        return $component = $this->components[$name] ?? null;
    }
}
