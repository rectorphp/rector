<?php

declare(strict_types=1);

namespace Nette\DI\Definitions;

if (class_exists('Nette\DI\Definitions\ServiceDefinition')) {
    return;
}

final class ServiceDefinition extends Definition
{
}
