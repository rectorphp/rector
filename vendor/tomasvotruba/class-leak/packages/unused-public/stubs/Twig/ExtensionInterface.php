<?php

declare (strict_types=1);
namespace RectorPrefix202609\Twig\Extension;

if (interface_exists(ExtensionInterface::class)) {
    return;
}
interface ExtensionInterface
{
    public function getLoader();
}
