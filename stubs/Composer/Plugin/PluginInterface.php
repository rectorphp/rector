<?php

declare(strict_types=1);

namespace Composer\Plugin;

if (interface_exists(\Composer\Plugin\PluginInterface::class)) {
    return;
}

// for downgrade process
interface PluginInterface
{
}
