<?php

declare(strict_types=1);

namespace Nette\Application\UI;

if (class_exists('Nette\Application\UI\Component')) {
    return;
}

abstract class Component extends \Nette\ComponentModel\Container implements ISignalReceiver, IStatePersistent, \ArrayAccess
{
}
