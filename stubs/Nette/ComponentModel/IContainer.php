<?php

declare(strict_types=1);

namespace Nette\ComponentModel;

// mimics: https://github.com/nette/component-model/blob/master/src/ComponentModel/IContainer.php

if (interface_exists('Nette\ComponentModel\IContainer')) {
    return;
}

interface IContainer extends IComponent
{
    /**
     * Adds the component to the container.
     * @return static
     */
    function addComponent(IComponent $component, ?string $name);

    /**
     * Removes the component from the container.
     */
    function removeComponent(IComponent $component): void;

    /**
     * Returns component specified by name or path.
     * @throws \Nette\InvalidArgumentException  if component doesn't exist
     */
    function getComponent(string $name): ?IComponent;

    /**
     * Iterates over descendants components.
     * @return \Iterator<int|string,IComponent>
     */
    function getComponents(): \Iterator;
}
