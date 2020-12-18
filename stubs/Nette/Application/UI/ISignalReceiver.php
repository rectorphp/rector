<?php

declare(strict_types=1);

namespace Nette\Application\UI;

if (interface_exists('Nette\Application\UI\ISignalReceiver')) {
    return;
}

interface ISignalReceiver
{
}
