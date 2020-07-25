<?php

declare(strict_types=1);

namespace Nette\ComponentModel;

if (interface_exists('Nette\ComponentModel\IContainer')) {
    return;
}

interface IContainer extends IComponent
{
}
