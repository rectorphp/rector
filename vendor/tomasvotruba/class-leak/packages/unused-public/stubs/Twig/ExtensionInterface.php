<?php

declare (strict_types=1);
namespace RectorPrefix202608\Twig\Extension;

if (interface_exists(ExtensionInterface::class)) {
    return;
}
interface ExtensionInterface
{
    public function getLoader();
}
